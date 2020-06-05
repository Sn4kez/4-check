<?php

use App\AccessGrant;
use App\Address;
use App\AddressType;
use App\ChoiceScheme;
use App\Company;
use App\Country;
use App\Checklist;
use App\Checkpoint;
use App\Directory;
use App\ArchiveDirectory;
use App\DirectoryEntry;
use App\Gender;
use App\Group;
use App\ParticipantExtension;
use App\Payment;
use App\Phone;
use App\PhoneType;
use App\Location;
use App\LocationExtension;
use App\PictureExtension;
use App\ReportSettings;
use App\Role;
use App\ScoreCondition;
use App\ScoringScheme;
use App\Score;
use App\Section;
use App\Sector;
use App\CompanySubscription;
use App\TextfieldExtension;
use App\User;
use App\ValueScheme;
use App\NotificationPreferences;
use Illuminate\Support\Str;
use PHPUnit\Framework\Assert as PHPUnit;
use Ramsey\Uuid\Uuid;
use App\Audit;
use App\AuditState;

abstract class TestCase extends Laravel\Lumen\Testing\TestCase {
    /**
     * @var string
     */
    public static $USER = 'user';

    /**
     * @var string
     */
    public static $USER_MAIL = 'user@4check.com';

    /**
     * @var string
     */
    public static $OTHER_USER = 'otherUser';

    /**
     * @var string
     */
    public static $ADMIN = 'admin';

    /**
     * @var string
     */
    public static $OTHER_ADMIN = 'otherAdmin';

    /**
     * @var string
     */
    public static $SUPER_ADMIN = 'superAdmin';

    /**
     * @var string
     */
    public static $PASSWORD = 'TestPassword123';

    /**
     * The base URL to use while testing the application.
     *
     * @var string
     */
    protected $baseUrl = 'http://localhost/v2';

    /**
     * A fake user, that can be used for testing.
     *
     * @var \App\User
     */
    protected $user;

    /**
     * Another fake user, that can be used for testing.
     *
     * @var \App\User
     */
    protected $otherUser;

    /**
     * A fake admin, that can be used for testing.
     *
     * @var \App\User
     */
    protected $admin;

    /**
     * Another fake admin, that can be used for testing.
     *
     * @var \App\User
     */
    protected $otherAdmin;

    /**
     * A super admin, that can be used for testing.
     *
     * @var \App\User
     */
    protected $superAdmin;

    /**
     * @var \App\Company
     */
    protected $company;

    /**
     * @var \App\Company
     */
    protected $otherCompany;

    /**
     * @var \App\Company
     */
    protected $companyWithoutSubscription;

    /**
     * Creates the application.
     *
     * @return \Laravel\Lumen\Application
     */
    public function createApplication() {
        return require __DIR__ . '/../bootstrap/app.php';
    }

    public function setUp() {
        parent::setUp();
        $company = $this->makeFakeCompany();
        $company->save();
        $company->companySubscription()->save(new CompanySubscription());
        $reportSettings = new ReportSettings();
        $reportSettings->id = Uuid::uuid4()->toString();
        $reportSettings->companyId = $company->id;
        $reportSettings->save();
        $company->reportSettings()->save($reportSettings);
        $company->directory()->save($this->makeFakeDirectory());
        $company->archive()->save($this->makeFakeArchiveDirectory());
        $this->company = $company->fresh();

        $otherCompany = $this->makeFakeCompany();
        $otherCompany->save();
        $otherCompany->companySubscription()->save(new CompanySubscription());
        $otherCompany->reportSettings()->save(new ReportSettings());
        $otherCompany->directory()->save($this->makeFakeDirectory());
        $otherCompany->archive()->save($this->makeFakeArchiveDirectory());
        $this->otherCompany = $otherCompany->fresh();

        $this->companyWithoutSubscription = $this->makeFakeCompany();
        $this->companyWithoutSubscription->save();

        $genders = Gender::all();
        $user = $this->makeFakeUser();
        $user->email = self::$USER_MAIL;
        $user->password = Hash::make(self::$PASSWORD);
        $user->firstName = '4check';
        $user->middleName = 'middlename';
        $user->lastName = 'testuser';
        $user->company()->associate($this->company);
        $user->gender()->associate($genders->random());
        $user->save();
        $this->user = $user->fresh();

        $otherUser = $this->makeFakeUser();
        $otherUser->password = Hash::make(self::$PASSWORD);
        $otherUser->firstName = 'other';
        $otherUser->middleName = 'middle name';
        $otherUser->lastName = 'test user';
        $otherUser->company()->associate($this->otherCompany);
        $otherUser->gender()->associate($genders->random());
        $otherUser->save();
        $this->otherUser = $otherUser->fresh();

        $admin = $this->makeFakeUser();
        $admin->password = Hash::make(self::$PASSWORD);
        $admin->role()->associate(Role::admin());
        $admin->company()->associate($this->company);
        $admin->gender()->associate($genders->random());
        $admin->save();
        $this->admin = $admin->fresh();
        $otherAdmin = $this->makeFakeUser();
        $otherAdmin->password = Hash::make(self::$PASSWORD);
        $otherAdmin->role()->associate(Role::admin());
        $otherAdmin->company()->associate($this->otherCompany);
        $otherAdmin->gender()->associate($genders->random());
        $otherAdmin->save();
        $this->otherAdmin = $otherAdmin->fresh();
        $superAdminsCompany = $this->makeFakeCompany();
        $superAdminsCompany->save();
        $superAdminsCompany->companySubscription()->save(new CompanySubscription());
        $superAdminsCompany->directory()->save($this->makeFakeDirectory());
        $superAdmin = $this->makeFakeUser();
        $superAdmin->role()->associate(Role::superAdmin());
        $superAdmin->company()->associate($superAdminsCompany);
        $superAdmin->gender()->associate($genders->random());
        $superAdmin->save();
        $this->superAdmin = $superAdmin->fresh();
    }

    /**
     * Get a fake user.
     *
     * @param string $key
     * @return \App\User;
     */
    public function getUser($key) {
        switch ($key) {
            case self::$USER:
                return $this->user;
            case self::$OTHER_USER:
                return $this->otherUser;
            case self::$ADMIN:
                return $this->admin;
            case self::$OTHER_ADMIN:
                return $this->otherAdmin;
            case self::$SUPER_ADMIN:
                return $this->superAdmin;
        }
        return null;
    }

    /**
     * Assert that the response contains the given JSON.
     *
     * @param  array $data
     * @param  bool $negate
     * @return $this
     */
    protected function seeJsonContains(array $data, $negate = false) {
        $method = $negate ? 'assertFalse' : 'assertTrue';

        $actual = json_decode($this->response->getContent(), true);

        if (is_null($actual) || $actual === false) {
            return PHPUnit::fail('Invalid JSON was returned from the route. Perhaps an exception was thrown?');
        }

        $actual = json_encode(array_sort_recursive((array)$actual));

        foreach (array_sort_recursive($data) as $key => $value) {
            $expected = $this->formatToExpectedJson($key, $value);

            if (is_array($value)) {
                $this->seeJsonContains($value, $negate);
                continue;
            }

            PHPUnit::{$method}(Str::contains($actual, $expected), ($negate ? 'Found unexpected' : 'Unable to find') . " JSON fragment [{$expected}] within [{$actual}].");
        }

        return $this;
    }

    /**
     * Asserts that the JSON response has non-null values for the given fields.
     *
     * @param array|null $structure
     * @param array|null $responseData
     * @return $this
     */
    public function seeJsonNotNull(array $structure = null, $responseData = null) {
        if (is_null($structure)) {
            return $this->seeJson();
        }
        if (!$responseData) {
            $responseData = json_decode($this->response->getContent(), true);
        }
        foreach ($structure as $key => $value) {
            if (is_array($value) && $key === '*') {
                PHPUnit::assertInternalType('array', $responseData);
                foreach ($responseData as $responseDataItem) {
                    $this->seeJsonNotNull($structure['*'], $responseDataItem);
                }
            } elseif (is_array($value)) {
                PHPUnit::assertArrayHasKey($key, $responseData);
                $this->seeJsonNotNull($structure[$key], $responseData[$key]);
            } else {
                PHPUnit::assertArrayHasKey($value, $responseData);
                PHPUnit::assertNotNull($responseData[$value]);
            }
        }
        return $this;
    }

    /**
     * @param $awaitedCount
     * @param string $dataKey
     * @return TestCase
     */
    public function countData($awaitedCount, $dataKey = 'data') {
        $responseData = json_decode($this->response->getContent(), true);
        $data = $responseData[$dataKey];

        return PHPUnit::assertEquals(count($data), $awaitedCount);
    }

    /**
     * Boot the testing helper traits.
     *
     * @return void
     */
    protected function setUpTraits() {
        parent::setUpTraits();
        $uses = array_flip(class_uses_recursive(get_class($this)));
        if (isset($uses[WithFaker::class])) {
            /** @var WithFaker $this */
            $this->setUpFaker();
        }
    }

    /**
     * Assert that the given record has been soft deleted.
     *
     * @param string $table
     * @param array $data
     * @param string $connection
     * @param string $column
     * @return $this
     */
    protected function assertSoftDeleted($table, array $data, $column = 'deleted_at', $connection = null) {
        $this->assertThat($table, new SoftDeleted($this->app->make('db')->connection($connection), $data, $column));
        return $this;
    }

    /**
     * Creates a new fake user.
     *
     * @param array $states
     * @return User
     */
    protected function makeFakeUser(array $states = []) {
        return factory(User::class)->states($states)->make();
    }

    /**
     * Creates a new fake company.
     *
     * @param array $states
     * @return Company
     */
    protected function makeFakeCompany(array $states = []) {
        /** @var Company $company */
        $company = factory(Company::class)->states($states)->make();
        $company->sector()->associate(Sector::all()->random());
        $company->id = Uuid::uuid4()->toString();
        return $company;
    }

    /**
     * Creates a new fake phone.
     *
     * @param string type
     * @param array $states
     * @return Phone
     */
    protected function makeFakePhone($type, array $states = []) {
        /** @var Phone $phone */
        $phone = factory(Phone::class)->states($states)->make();
        $phone->type()->associate(PhoneType::find($type));
        return $phone;
    }

    /**
     * Creates a new fake address.
     *
     * @param string type
     * @param array $states
     * @return Address
     */
    protected function makeFakeAddress($type, array $states = []) {
        /** @var Address $address */
        $address = factory(Address::class)->states($states)->make();
        $address->country()->associate(Country::all()->random());
        $address->type()->associate(AddressType::find($type));
        return $address;
    }

    /**
     * Creates a new fake group.
     *
     * @param array $states
     * @return Group
     */
    protected function makeFakeGroup(array $states = []) {
        return factory(Group::class)->states($states)->make();
    }

    /**
     * Creates a new fake directory.
     *
     * @param array $states
     * @return Directory
     */
    protected function makeFakeDirectory(array $states = []) {
        return factory(Directory::class)->states($states)->make();
    }

    /**
     * Creates a new fake directory entry.
     *
     * @return DirectoryEntry
     */
    protected function makeFakeDirectoryEntry() {
        return factory(DirectoryEntry::class)->make();
    }

    /**
     * Creates a new fake archive.
     *
     * @param array $states
     * @return Directory
     */
    protected function makeFakeArchiveDirectory(array $states = []) {
        return factory(ArchiveDirectory::class)->states($states)->make();
    }

    /**
     * Creates a new fake access grant.
     *
     * @param boolean $allowed
     * @param array $states
     * @return AccessGrant
     */
    protected function makeFakeAccessGrant($allowed = true, array $states = []) {
        /** @var AccessGrant $grant */
        $grant = factory(AccessGrant::class)->states($states)->make();
        $grant->view = $allowed;
        $grant->index = $allowed;
        $grant->update = $allowed;
        $grant->delete = $allowed;
        return $grant;
    }

    /**
     * Creates a new fake checklist.
     *
     * @param array $states
     * @return Checklist
     */
    protected function makeFakeChecklist(array $states = []) {
        return factory(Checklist::class)->states($states)->make();
    }

    /**
     * Creates a new fake audit
     *
     * @return \App\Audit
     */
    protected function makeFakeAudit() {
        return factory(Audit::class)->make();
    }

    /**
     * Creates a new fake audit state
     *
     * @return \App\AuditState
     *
     */
    protected function makeFakeAuditState() {
        return factory(AuditState::class)->make();
    }

    /**
     * Creates a new fake section.
     *
     * @param array $states
     * @return Section
     */
    protected function makeFakeSection(array $states = []) {
        return factory(Section::class)->states($states)->make();
    }

    /**
     * Creates a new fake scoring scheme.
     *
     * @param array $states
     * @return ScoringScheme
     */
    protected function makeFakeScoringScheme(array $states = []) {
        return factory(ScoringScheme::class)->states($states)->make();
    }

    /**
     * Creates a new fake score.
     *
     * @param array $states
     * @return ScoringScheme
     */
    protected function makeFakeScore(array $states = []) {
        return factory(Score::class)->states($states)->make();
    }

    /**
     * Creates a new fake checkpoint.
     *
     * @param array $states
     * @return Checkpoint
     */
    protected function makeFakeCheckpoint(array $states = []) {
        return factory(Checkpoint::class)->states($states)->make();
    }

    /**
     * Creates a new fake choice scheme.
     *
     * @param array $states
     * @return ChoiceScheme
     */
    protected function makeFakeChoiceScheme(array $states = []) {
        return factory(ChoiceScheme::class)->states($states)->make();
    }

    /**
     * Creates a new fake value scheme.
     *
     * @param array $states
     * @return ValueScheme
     */
    protected function makeFakeValueScheme(array $states = []) {
        return factory(ValueScheme::class)->states($states)->make();
    }

    /**
     * Creates a new fake score condition.
     *
     * @param array $states
     * @return ScoreCondition
     */
    protected function makeFakeScoreCondition(array $states = []) {
        return factory(ScoreCondition::class)->states($states)->make();
    }

    /**
     * Creates a new fake textfield extension.
     *
     * @param array $states
     * @return TextfieldExtension
     */
    protected function makeFakeTextfieldExtension(array $states = []) {
        return factory(TextfieldExtension::class)->states($states)->make();
    }

    /**
     * Creates a new fake picture extension.
     *
     * @return TextfieldExtension
     */
    protected function makeFakePictureExtension(array $states = []) {
        return factory(PictureExtension::class)->make();
    }

    /**
     * Creates a new fake location extension.
     *
     * @param array $states
     * @return LocationExtension
     */
    protected function makeFakeLocationExtension(array $states = []) {
        return factory(LocationExtension::class)->states($states)->make();
    }

    /**
     * Creates a new fake location extension.
     *
     * @param array $states
     * @return LocationExtension
     */
    protected function makeFakeCheckExtension(array $states = []) {
        return factory(LocationExtension::class)->states($states)->make();
    }

    /**
     * Creates a new fake participant extension.
     *
     * @param array $states
     * @return ParticipantExtension
     */
    protected function makeFakeParticipantExtension(array $states = []) {
        return factory(ParticipantExtension::class)->states($states)->make();
    }

    /**
     * Creates a new fake location.
     *
     * @param array $states
     * @return Location
     */
    protected function makeFakeLocation(array $states = []) {
        return factory(Location::class)->states($states)->make();
    }

    /**
     * @param int $allowed 0 or 1
     * @return NotificationPreferences
     */
    protected function makeFakeNotificationPreferences($allowed) {
        $preferences = new NotificationPreferences();
        $preferences->id = Uuid::uuid4()->toString();

        $preferences->checklistNeedsActivityNotification = $allowed;
        $preferences->checklistCompletedNotification = $allowed;
        $preferences->checklistDueNotification = $allowed;
        $preferences->checklistAssignedNotification = $allowed;
        $preferences->checklistCriticalRatingNotification = $allowed;

        $preferences->taskCompletedNotification = $allowed;
        $preferences->taskAssignedNotification = $allowed;
        $preferences->taskUpdatedNotification = $allowed;
        $preferences->taskDeletedNotification = $allowed;

        $preferences->checklistNeedsActivityMail = $allowed;
        $preferences->checklistCompletedMail = $allowed;
        $preferences->checklistDueMail = $allowed;
        $preferences->checklistAssignedMail = $allowed;
        $preferences->checklistCriticalRatingMail = $allowed;

        $preferences->taskCompletedMail = $allowed;
        $preferences->taskAssignedMail = $allowed;
        $preferences->taskUpdatedMail = $allowed;
        $preferences->taskDeletedMail = $allowed;

        $preferences->auditAssignedNotification = $allowed;
        $preferences->auditCompletedNotification = $allowed;
        $preferences->auditOverdueNotification = $allowed;
        $preferences->auditReleaseRequiredNotification = $allowed;

        return $preferences;
    }

    public function getDataFromLastResponse($key, $default = null) {
        $response = json_decode($this->response->getContent(), true);

        if (array_key_exists($key, $response)) {
            return $response[$key];
        } else {
            if (array_key_exists('data', $response)) {
                if (array_key_exists($key, $response['data'])) {
                    return $response['data'][$key];
                }
            }
        }
    }

    public function ddLastContent() {
        dd(sprintf("STATUS: %s, DATA: %s", $this->response->getStatusCode(), $this->response->getContent()));
    }

    public function createTestPayment() {
        $data = [
            'company' => 'Test Company',
            'street' => 'Teststrasse',
            'housenumber' => '123',
            'postcode' => '22222',
            'city' => 'Hamburg',
            'country' => 'DE',
            'token' => null,
            Payment::PARAM_NAME_METHOD => Payment::METHOD_NAME_INVOICE
        ];

        $this->json('POST', '/payment/create', array_merge([
            Payment::PARAM_NAME_PACKAGE => Payment::SUBSCRIPTION_KEY_BASIC_MONTHLY_DEV,
            Payment::PARAM_NAME_QUANTITY => 1,
            Payment::PARAM_NAME_REFERENCE => 'test reference'
        ], $data));
    }
}
