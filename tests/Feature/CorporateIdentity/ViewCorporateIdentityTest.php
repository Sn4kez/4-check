<?php

use App\Company;
use App\Corporateidentity;
use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;
use App\Media;

class ViewCorporateIdentityTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * @var \App\CorporateIdentity $ci
     */
    protected $ci;

    private $mediaBase64ContentCheckString;

    public function setUp()
    {
        parent::setUp();

        $uploadFeedback = Media::uploadUnitTestTestImage(sprintf("%s/files/logo.jpg", dirname(__FILE__)));
        $this->mediaBase64ContentCheckString = $uploadFeedback["base64Content"];
        $mediaName = $uploadFeedback["mediaName"];

        $this->ci = new CorporateIdentity();
        $this->ci->brand_primary = '#000000';
        $this->ci->brand_secondary = '#FFFFFF';
        $this->ci->link_color = '#FF0000';
        $this->ci->image = $mediaName;
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
        $this->json('GET', '/companies/preferences/ci/' . $this->ci->company->id);
        $this->seeStatusCode($statusCode);
        $this->seeHeader('Content-Type', 'application/json');
    }

    public function provideValidAccessData()
    {
        return [
            [self::$ADMIN],
            [self::$SUPER_ADMIN],
        ];
    }

    /**
     * @param string $userKey
     * @dataProvider provideValidAccessData
     */
    public function testValidAccess($userKey)
    {
        $user = $this->getUser($userKey);
        $this->actingAs($user);

        $this->json('GET', '/companies/preferences/ci/' . $this->ci->company->id);
        $this->seeStatusCode(Response::HTTP_OK);
        $this->seeHeader('Content-Type', 'application/json');
        $this->seeJsonStructure([
            'data' => [
            	'id',
                'company',
                'brand_primary',
                'brand_secondary',
                'link_color',
                'image'
            ],
        ])->seeJsonNotNull([
            'data' => [
                'id',
                'company',
                'brand_primary',
                'brand_secondary',
                'link_color',
                'image'
            ],
        ])->seeJsonContains([
            'company' => $this->ci->company->id,
            'brand_primary' => $this->ci->brand_primary,
            'brand_secondary' => $this->ci->brand_secondary,
            'link_color' => $this->ci->link_color,
            'image' => $this->mediaBase64ContentCheckString
        ]);
    }
}
