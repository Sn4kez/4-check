<?php

use App\Company;
use App\Gender;
use App\Phone;
use App\Role;
use App\Sector;
use App\User;
use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;

class CreateUserTest extends TestCase
{
    use DatabaseMigrations;

    public function testValidAccess()
    {
        $user = factory(User::class)->make();
        $user->gender()->associate(Gender::all()->random());
        $company = factory(Company::class)->make();
        $company->sector()->associate(Sector::find('cleaning'));
        $phone = factory(Phone::class)->make();
        $this->json('POST', '/users', [
            'email' => $user->email,
            'password' => 'Secret123',
            'gender' => $user->gender->id,
            'company' => [
                'name' => $company->name,
                'sector' => $company->sector->id,
            ],
            'phone' => [
                'countryCode' => $phone->countryCode,
                'nationalNumber' => $phone->countryCode,
            ],
        ]);
        $this->seeStatusCode(Response::HTTP_CREATED);
        $this->seeHeader('Content-Type', 'application/json');
        $this->seeJsonStructure([
            'data' => [
                'id',
                'email',
                'companyId',
            ],
        ])->seeJsonNotNull([
            'data' => [
                'id',
                'companyId',
            ],
        ])->seeJsonContains([
            'email' => $user->email,
            'role' => Role::$ADMIN,
        ]);
        $this->seeInDatabase('users', [
            'email' => $user->email,
        ]);
        $this->seeInDatabase('companies', [
            'name' => $company->name,
            'sectorId' => $company->sector->id,
        ]);
        $user = User::whereEmail($user->email)->firstOrFail();
        $this->assertEquals($user->company->name, $company->name);
    }

    public function provideValidEntities()
    {
        return [
            ['firstName', 'firstName', 'firstName', 'firstName'],
            ['middleName', 'middleName', 'middleName', 'middleName'],
            ['lastName', 'lastName', 'lastName', 'lastName'],
            ['locale', 'locale', 'localeId', 'de'],
            ['timezone', 'timezone', 'timezone', 'Europe/Berlin'],
            ['source_b64', 'image', 'image', 'logo.jpg'],
            ['source_b64', 'image', 'image', null],
        ];
    }

    /**
     * @param $attribute
     * @param $jsonAttribute
     * @param $dbAttribute
     * @param $value
     * @dataProvider provideValidEntities
     */
    public function testValidEntities($attribute, $jsonAttribute, $dbAttribute, $value)
    {
        $user = factory(User::class)->make();
        $user->gender()->associate(Gender::all()->random());
        $company = factory(Company::class)->make();
        $company->sector()->associate(Sector::find('cleaning'));
        $phone = factory(Phone::class)->make();

        if ($attribute === "source_b64" && !is_null($value)) {
            $fileLocation = sprintf("%s/files/%s", dirname(__FILE__), $value);

            if (file_exists($fileLocation)) {
                $value = base64_encode(file_get_contents($fileLocation));
            }
        }

        $data = array_merge([
            'email' => $user->email,
            'password' => 'Secret123',
            'gender' => $user->gender->id,
            'company' => [
                'name' => $company->name,
                'sector' => $company->sector->id,
            ],
            'phone' => [
                'countryCode' => $phone->countryCode,
                'nationalNumber' => $phone->countryCode,
            ],
        ], [$attribute => $value]);
        $this->json('POST', '/users/', $data);
        $this->seeStatusCode(Response::HTTP_CREATED);
        $this->seeHeader('Content-Type', 'application/json');

        $this->seeJsonStructure([
            'data' => [
                $jsonAttribute
            ],
        ]);

        if ($jsonAttribute !== 'image') {
            /**
             * We can´t check for images, because they were uploaded and getting a filename.
             * So we just could check the image name, which we dont have here
             */
            $this->seeJsonContains([
                'email' => $user->email,
                $jsonAttribute => $value
            ]);

            $this->seeInDatabase('users', [
                'email' => $user->email,
                $dbAttribute => $value
            ]);
        } else {
            if($value !== null) {
                $this->seeJsonNotNull([
                    'data' => [
                        $jsonAttribute
                    ]
                ]);
            }
        }
    }

    public function provideInvalidEntities()
    {
        return [
            ['email', null],
            ['email', 'very@' . str_repeat('long.', 25) . 'example.com'],
            ['email', 'invalid@email'],
            ['email', 'userMail'],
            ['password', null],
            ['password', 'Sh0rt'],
            ['password', 'Secret 123'],
            ['password', 'n0upperc4se'],
            ['password', 'N0LOWERC4SE'],
            ['password', 'NoNumber'],
            ['firstName', 123],
            ['firstName', str_repeat('Long', 32) . 'Name'],
            ['middleName', 123],
            ['middleName', str_repeat('Long', 32) . 'Name'],
            ['lastName', 123],
            ['lastName', str_repeat('Long', 32) . 'Name'],
            ['locale', 123],
            ['locale', 'unknown'],
            ['timezone', 123],
            ['timezone', 'unknown'],
            ['company', ['name' => null]],
            ['company', ['name' => str_repeat('Long', 32) . 'Name']],
            ['company', ['name' => 'company', 'sector' => null]],
            ['company', ['name' => 'company', 'sector' => 123]],
            ['company', ['name' => 'company', 'sector' => 'unknown']],
        ];
    }

    /**
     * @param string $attribute
     * @param string $value
     * @dataProvider provideInvalidEntities
     */
    public function testInvalidEntities($attribute, $value)
    {
        if ($value === 'userMail') {
            $value = $this->user->email;
        }
        $user = factory(User::class)->make();
        $user->gender()->associate(Gender::all()->random());
        $company = factory(Company::class)->make();
        $company->sector()->associate(Sector::find('cleaning'));
        $phone = factory(Phone::class)->make();
        $data = array_merge([
            'email' => $user->email,
            'password' => 'Secret123',
            'gender' => $user->gender->id,
            'company' => [
                'name' => $company->name,
                'sector' => $company->sector->id,
            ],
            'phone' => [
                'countryCode' => $phone->countryCode,
                'nationalNumber' => $phone->countryCode,
            ],
        ], [$attribute => $value]);
        $this->json('POST', '/users/', $data);
        $this->seeStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->seeHeader('Content-Type', 'application/json');
    }
}
