<?php

use App\Company;
use App\CorporateIdentity;
use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;

class UpdateCorporateIdentityTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * @var \App\CorporateIdentity $ci
     */
    protected $ci;

    public function setUp()
    {
        parent::setUp();

        $this->ci = new CorporateIdentity();
        $this->ci->brand_primary = '#000000';
        $this->ci->brand_secondary = '#FFFFFF';
        $this->ci->link_color = '#FF0000';
        $this->ci->company()->associate($this->company);
        $this->ci->save();
    }

    public function provideInvalidAccessData()
    {
        return [
            [null, Response::HTTP_UNAUTHORIZED],
            [self::$USER, Response::HTTP_UNAUTHORIZED]
        ];
    }

    /**
     * @param string $userKey
     * @param string $userIdKey
     * @param int $statusCode
     * @dataProvider provideInvalidAccessData
     */
    public function testInvalidAccess($userKey, $statusCode)
    {
        $this->json('PATCH', '/companies/preferences/ci/' . $this->ci->company->id);
        $this->seeStatusCode($statusCode);
        $this->seeHeader('Content-Type', 'application/json');
    }

    public function provideValidEntities()
    {
        return [
        	[self::$ADMIN, 'brand_primary', 'brand_primary', 'red'],
            [self::$ADMIN, 'brand_primary', 'brand_primary', '#FF0000'],
            [self::$ADMIN, 'brand_primary', 'brand_primary', 'rbg(255,255,255)'],
            [self::$ADMIN, 'brand_primary', 'brand_primary', 'rgba(255,255,255,0.2)'],
            [self::$ADMIN, 'brand_primary', 'brand_primary', 'hsl(0,100%,50%)'],
            [self::$ADMIN, 'brand_primary', 'brand_primary', 'hsla(0,100%,50%,0)'],
            [self::$SUPER_ADMIN, 'brand_primary', 'brand_primary', 'red'],
            [self::$SUPER_ADMIN, 'brand_primary', 'brand_primary', '#FF0000'],
            [self::$SUPER_ADMIN, 'brand_primary', 'brand_primary', 'rbg(255,255,255)'],
            [self::$SUPER_ADMIN, 'brand_primary', 'brand_primary', 'rgba(255,255,255,0.2)'],
            [self::$SUPER_ADMIN, 'brand_primary', 'brand_primary', 'hsl(0,100%,50%)'],
            [self::$SUPER_ADMIN, 'brand_primary', 'brand_primary', 'hsla(0,100%,50%,0)'],
            [self::$ADMIN, 'source_b64', 'source_b64', 'logo.jpg'],
            [self::$SUPER_ADMIN, 'source_b64', 'source_b64', 'logo.jpg'],
            [self::$ADMIN, 'source_b64', 'source_b64', null],
            [self::$SUPER_ADMIN, 'source_b64', 'source_b64', null],
        ];
    }

    /**
     * @param $userKey
     * @param $attribute
     * @param $dbAttribute
     * @param $value
     * @dataProvider provideValidEntities
     */
    public function testValidEntities($userKey, $attribute, $dbAttribute, $value)
    {
        $user = $this->getUser($userKey);
        $this->actingAs($user);

        if ($attribute === "source_b64" && !is_null($value)) {
            $fileLocation = sprintf("%s/files/%s", dirname(__FILE__), $value);

            if (file_exists($fileLocation)) {
                $value = base64_encode(file_get_contents($fileLocation));
            }
        }

        $data = [$attribute => $value];

        $this->json('PATCH', '/companies/preferences/ci/' . $this->ci->company->id, $data);

        $this->seeStatusCode(Response::HTTP_NO_CONTENT);

        if($attribute !== 'source_b64') {
            $this->seeInDatabase('corporate_identities', [
                'id' => $this->ci->id,
                $dbAttribute => $value,
            ]);
        }
    }
}
