<?php

namespace App\Http\Controllers;

use App\Company;
use App\Directory;
use App\ArchiveDirectory;
use App\Gender;
use App\Http\Resources\UserResource;
use App\Locale;
use App\Payment;
use App\Phone;
use App\PhoneType;
use App\ReportSettings;
use App\Role;
use App\Sector;
use App\User;
use App\CorporateIdentity;
use App\CorporateIdentityLogin;

use App\Events\User\UserCreateEvent;
use App\Events\PasswordReset\PasswordResetTokenSetEvent;
use App\Events\PasswordReset\PasswordResetConfirmationEvent;
use App\Events\Company\CompanyCreateEvent;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Ramsey\Uuid\Uuid;
use Carbon\Carbon;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use App\Media;


class UserController extends Controller
{
    /**
     * @var User
     */
    protected $user;

    /**
     * @var Company
     */
    protected $company;

    /**
     * @var Locale
     */
    protected $locale;

    /**
     * @var Gender
     */
    protected $gender;

    /**
     * @var Sector
     */
    protected $sector;

    /**
     * @var Directory
     */
    protected $directory;

    /**
     * @var Phone
     */
    protected $phone;

    /**
     * @var PhoneType
     */
    protected $phoneType;

    /**
     * @var ReportSettings
     */
    protected $reportSettings;

    /**
     * @var Role
     */
    protected $role;

    /**
     * Create a new controller instance.
     *
     * @param User $user
     * @param Locale $locale
     * @param Gender $gender
     * @param Company $company
     * @param Sector $sector
     * @param Directory $directory
     * @param Phone $phone
     * @param PhoneType $phoneType
     * @param ReportSettings $reportSettings
     * @param Role $role
     */
    public function __construct(User $user, Locale $locale, Gender $gender,
                                Company $company, Sector $sector, Directory $directory,
                                Phone $phone, PhoneType $phoneType,
                                ReportSettings $reportSettings, Role $role, ArchiveDirectory $archive) {
        $this->user = $user;
        $this->locale = $locale;
        $this->gender = $gender;
        $this->company = $company;
        $this->archive = $archive;
        $this->sector = $sector;
        $this->directory = $directory;
        $this->phone = $phone;
        $this->phoneType = $phoneType;
        $this->reportSettings = $reportSettings;
        $this->role = $role;
    }

    /**
     * Create a new user.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(Request $request) {
        // TODO: Refactor this into smaller functions.
        $data = $this->validate($request, User::rules('create'));
        $data['password'] = Hash::make($data['password']);

        $data = Media::processRequest($data);

        /* @var User $user */
        $user = $this->createUser($data);

        /* @var Company $company */
        $company = $this->createCompany($data);

        $company->reportSettings()->save($this->createNewReportSetting());
        $company->directory()->save($this->createDirectory());
        $company->archive()->save($this->createArchive());

        $user->company()->associate($company);
        $user->role()->associate(Role::admin());
        $user->token = Uuid::uuid4()->toString();
        $user->save();

        $user->phones()->save($this->createPhone($data));

        event(new UserCreateEvent($user));

        return UserResource::make($user->fresh())
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    private function createUser($data) {
        $user = $this->user->newModelInstance($data);

        if (array_key_exists('locale', $data)) {
            $locale = $this->locale->findOrFail($data['locale']);
            $user->locale()->associate($locale);
        }

        $gender = $this->gender->findOrFail($data['gender']);
        $user->gender()->associate($gender);

        return $user;
    }

    private function createCompany($data) {
        $company = $this->company->newModelInstance($data['company']);

        if (array_key_exists('sector', $data['company'])) {
            $sector = $this->sector->findOrFail($data['company']['sector']);
            $company->sector()->associate($sector);
        }

        $company->save();

        $ci = new CorporateIdentity();
        $ci->company()->associate($company);
        $ci->save();

        $brandedLogin = new CorporateIdentityLogin();
        $brandedLogin->id = $company->id;
        $brandedLogin->corporateIdentity()->associate($ci);
        $brandedLogin->save();

        //event(new CompanyCreateEvent($company));

        return $company;
    }

    private function createNewReportSetting() {
        return $this->reportSettings->newModelInstance();
    }

    private function createDirectory() {
        return $this->directory->newModelInstance(['name' => 'Home']);
    }

    private function createArchive() {
        return $this->archive->newModelInstance(['name' => 'Archive Home']);
    }

    private function createPhone($data) {
        $phone = $this->phone->newModelInstance($data['phone']);
        $type = $this->phoneType->findOrFail('work');
        $phone->type()->associate($type);

        return $phone;
    }

    /**
     * List all users, optionally filtering by company.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request) {
        /* @var User $loggedInUser */
        $loggedInUser = $request->user();
        $companyId = $request->get('company');
        /* @var Company $company */
        if ($companyId == null) {
            if ($loggedInUser->isSuperAdmin()) {
                $users = $this->user->all();
            } else {
                $company = $loggedInUser->company;
                $this->authorize('view', $company);
                $users = $company->users;
            }
        } else {
            $company = $this->company->findOrFail($companyId);
            $this->authorize('view', $company);
            $users = $company->users;
        }
        return UserResource::collection($users)
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    /**
     * View a user.
     *
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function view(string $id) {
        $user = Media::changeImageFilenameToBase64String($this->user->findOrFail($id));
        $this->authorize($user);
        return UserResource::make($user)
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    /**
     * View the currently logging in user.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function viewMe() {
        $userModel = $this->addPaymentInformation(Media::changeImageFilenameToBase64String(Auth::user()));

        return UserResource::make($userModel)
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    public function addPaymentInformation(User $user) {
        $user->current_package = Payment::getCompanyPackageNameByUser($user);

        return $user;
    }

    /**
     * Update a user.
     *
     * @param Request $request
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, string $id) {
        $sendPasswordEvent = false;

        /* @var User $user */
        $user = $this->user->findOrFail($id);
        $this->authorize($user);
        $data = $this->validate($request, User::rules('update', [], $user->id));
        if (array_key_exists('newPassword', $data)) {
            if (Hash::check($data['currentPassword'], $user->password)) {
                $data['password'] = Hash::make($data['newPassword']);
                $sendPasswordEvent = true;
            } else {
                throw new UnauthorizedHttpException('The current password is invalid.');
            }
        }
        if (array_key_exists('locale', $data)) {
            $locale = $this->locale->findOrFail($data['locale']);
            $user->locale()->associate($locale);
        }
        if (array_key_exists('gender', $data)) {
            $gender = $this->gender->findOrFail($data['gender']);
            $user->gender()->associate($gender);
        }
        if (array_key_exists('role', $data)) {
            if (Auth::user()->is($user)) {
                //throw new UnauthorizedHttpException('Cannot change own role.');
            } else {
                $role = $this->role->findOrFail($data['role']);
                $user->role()->associate($role);
            }
        }

        $data = Media::processRequest($data);

        $user->update($data);

        if ($sendPasswordEvent) {
            //event(new PasswordResetConfirmationEvent($user));
        }

        return response('', Response::HTTP_NO_CONTENT);
    }

    /**
     * Delete a user.
     *
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(string $id) {
        $user = $this->user->findOrFail($id);
        $this->authorize($user);
        $user->delete();
        return response('', Response::HTTP_NO_CONTENT);
    }

    /**
     * sets a token to reset a password
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */

    public function setResetPasswordToken(Request $request) {
        $data = $this->validate($request, User::rules('resetPasswordToken'));

        $user = $this->user->where('email', '=', $data['email'])->firstOrFail();
        $user->token = Uuid::uuid4()->toString();
        $user->tokenCreatedAt = Carbon::now();
        $user->save();

        event(new PasswordResetTokenSetEvent($user));

        return response('', Response::HTTP_OK);
    }

    /**
     * resets password
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */

    public function resetPassword(Request $request) {
        $data = $this->validate($request, User::rules('resetPassword'));

        $user = $this->user->where('token', '=', $data['token'])->first();

        if ($user->tokenCreatedAt < Carbon::now()->subMinutes(30)) {
            return response(__('tokenExpired'), Response::HTTP_BAD_REQUEST);
        }

        $user->password = Hash::make($data['password']);
        $user->token = '';
        $user->save();

        event(new PasswordResetConfirmationEvent($user));

        return response('', Response::HTTP_OK);
    }

    /**
     * validates email
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */

    public function verifyEmail(Request $request) {
        $data = $this->validate($request, User::rules('verifyEmail'));

        $user = $this->user->where('token', '=', $data['token'])->where('emailVerified', '=', 0)->first();

        $user->token = '';
        $user->emailVerified = 1;
        $user->save();

        //event(new PasswordResetConfirmationEvent($user));

        return response('', Response::HTTP_OK);
    }
}
