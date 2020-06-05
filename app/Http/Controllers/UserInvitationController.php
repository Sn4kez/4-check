<?php

namespace App\Http\Controllers;

use App\Payment;
use App\UserInvitation;
use App\Company;
use App\User;
use App\Media;
use App\Events\Registration\NewUserInvitationEvent;
use App\Events\User\InvitedUserRegistration;
use App\Http\Resources\UserInvitationResource;
use App\Http\Resources\UserInvitationWithCompanyResource;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Ramsey\Uuid\Uuid;

use App\Gender;
use App\Locale;
use App\Phone;
use App\PhoneType;
use App\Role;

class UserInvitationController extends Controller {
    /**
     * @var UserInvitation
     */

    private $invitation;

    /**
     * Create a new controller instance.
     *
     * @param UserInvitation $invitation
     */

    public function __contsruct(UserInvitation $invitation) {
        $this->invitation = $invitation;
    }

    /**
     * Create a new invitation.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */

    public function create(Request $request) {
        $data = $this->validate($request, UserInvitation::rules('create'));

        $company = Company::findOrFail($data['company']);

        $invitation = new UserInvitation($data);
        $invitation->token = Uuid::uuid4()->toString();
        $invitation->tokenCreatedAt = Carbon::now();
        $invitation->company()->associate($company);

        $this->authorize($invitation);

        $invitation->save();

        event(new NewUserInvitationEvent($invitation, $request->user()));

        Payment::incrementCompanySubscriptionIfNeeded($company);

        return UserInvitationResource::make($invitation)->response()->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * Update a invitation.
     *
     * @param Request $request
     * @param string $token
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */

    public function update(Request $request, string $token) {
        $data = $this->validate($request, UserInvitation::rules('update'));

        $invitation = UserInvitation::where('token', '=', $token)->firstOrFail();

        $this->authorize($invitation);

        $invitation->token = Uuid::uuid4()->toString();
        $invitation->fill($data);
        $invitation->tokenCreatedAt = Carbon::now();
        $invitation->save();

        event(new NewUserInvitationEvent($invitation, $request->user()));

        return UserInvitationResource::make($invitation)->response()->setStatusCode(Response::HTTP_NO_CONTENT);
    }

    /**
     * Deletes a invitation.
     *
     * @param Request $request
     * @param string $token
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */

    public function delete(Request $request, string $token) {
        $invitation = UserInvitation::where('token', '=', $token)->firstOrFail();

        $this->authorize($invitation);

        $invitation->delete();

        return Response('', Response::HTTP_NO_CONTENT);
    }

    /**
     * List a companies invitations.
     *
     * @param Request $request
     * @param string|null $company
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request, string $company = NULL) {
        if (!$request->user()->isSuperAdmin() && (!$request->user()->isAdmin() || $request->user()->company->is(Company::find($company)))) {
            Response('', Response::HTTP_FORBIDDEN);
        }

        $invitations = UserInvitation::where('companyId', '=', $company);

        return UserInvitationResource::collection($invitations->get())->response()->setStatusCode(Response::HTTP_OK);
    }

    /**
     * List a companies invitations.
     *
     * @param Request $request
     * @param string|null $companyId
     * @return \Illuminate\Http\JsonResponse
     */
    public function view(Request $request, string $token) {
        $invitation = UserInvitation::where('token', '=', $token)->firstOrFail();

        $this->authorize($invitation);

        return UserInvitationResource::make($invitation)->response()->setStatusCode(Response::HTTP_OK);
    }

    /**
     * Registers user if token is correct
     *
     * @param Request $request
     * @param string $token
     * @return \Illuminate\Http\JsonResponse
     */

    public function registerUser(Request $request, string $token) {
        //$data = $this->validate($request, UserInvitation::rules('accept'));

        $data = $request->all();

        $invitation = UserInvitation::where('token', '=', $token)->firstOrFail();

        $data['password'] = Hash::make($data['password']);

        $data = Media::processRequest($data);

        $user = new User($data);
        $user->email = $invitation->email;
        $user->company()->associate($invitation->company);

        if (array_key_exists('locale', $data)) {
            $locale = Locale::findOrFail($data['locale']);
            $user->locale()->associate($locale);
        }
        $gender = Gender::findOrFail($data['gender']);
        $user->gender()->associate($gender);

        $user->save();

        $phone = new Phone($data['phone']);
        $type = PhoneType::findOrFail('work');
        $phone->type()->associate($type);
        $user->phones()->save($phone);

        event(new InvitedUserRegistration($user));

        return UserResource::make($user->fresh())->response()->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * provides data to prefill in form
     *
     * @param Request $request
     * @param string $token
     * @return \Illuminate\Http\JsonResponse
     */

    public function provideData(Request $request, string $token) {
        $invitation = UserInvitation::where('token', '=', $token)->firstOrFail();

        if ($invitation->tokenCreatedAt < Carbon::now()->subDays(14)) {
            return response('tokenExpired', Response::HTTP_BAD_REQUEST);
        }

        return UserInvitationWithCompanyResource::make($invitation)->response()->setStatusCode(Response::HTTP_OK);
    }
}
