<?php

use App\Company;
use App\CorporateIdentity;
use App\CorporateIdentityLogin;
use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Ramsey\Uuid\Uuid;
use App\Media;

class ViewCorporateLoginTest extends TestCase
{
    use DatabaseMigrations;

    private $login;
    private $ci;

    private $ci2;
    private $login2;

    private $mediaBase64ContentCheckString = "";

    public function setUp()
    {
        parent::setUp();

        $uploadFeedback = Media::uploadUnitTestTestImage(sprintf("%s/files/logo.png", dirname(__FILE__)));
        $this->mediaBase64ContentCheckString = $uploadFeedback["base64Content"];
        $mediaName = $uploadFeedback["mediaName"];

        $this->ci = new CorporateIdentity();
        $this->ci->id = Uuid::uuid4()->toString();
        $this->ci->brand_primary = '#000000';
        $this->ci->brand_secondary = '#FFFFFF';
        $this->ci->link_color = '#FF0000';
        $this->ci->image = $mediaName;
        $this->ci->company()->associate($this->company);
        $this->ci->save();

        $this->ci2 = new CorporateIdentity();
        $this->ci2->id = Uuid::uuid4()->toString();
        $this->ci2->brand_primary = '#555555';
        $this->ci2->brand_secondary = '#555555';
        $this->ci2->link_color = '#555555';
        $this->ci2->image = null;
        $this->ci2->company()->associate($this->otherCompany);
        $this->ci2->save();

        $this->login = new CorporateIdentityLogin();
        $this->login->id = "test123";
        $this->login->corporateIdentity()->associate($this->ci);
        $this->login->save();

        $this->login2 = new CorporateIdentityLogin();
        $this->login2->id = "1234";
        $this->login2->corporateIdentity()->associate($this->ci2);
        $this->login2->save();
    }

    public function testValidAccessData()
    {
        $seeStructure = [
            'id',
            'company',
            'brand_primary',
            'brand_secondary',
            'link_color'
        ];

        $seeNotNull = $seeStructure;

        /**
         * Check CI login WITH image
         */
        $this->json('GET', '/login/ci/' . $this->login->id);
        $this->seeStatusCode(Response::HTTP_OK);
        $this->seeHeader('Content-Type', 'application/json');
        $this->seeJsonStructure([
            'data' => array_merge($seeStructure, ['image']),
        ])->seeJsonNotNull([
            'data' => array_merge($seeNotNull, ['image']),
        ])->seeJsonContains([
            'company' => $this->ci->company->id,
            'brand_primary' => $this->ci->brand_primary,
            'brand_secondary' => $this->ci->brand_secondary,
            'link_color' => $this->ci->link_color,
            'image' => $this->mediaBase64ContentCheckString
        ]);

        /**
         * Check CI login WITHOUT image
         */
        $this->json('GET', '/login/ci/' . $this->login2->id);
        $this->seeStatusCode(Response::HTTP_OK);
        $this->seeHeader('Content-Type', 'application/json');
        $this->seeJsonStructure([
            'data' => $seeStructure,
        ])->seeJsonNotNull([
            'data' => $seeNotNull,
        ])->seeJsonContains([
            'company' => $this->ci2->company->id,
            'brand_primary' => $this->ci2->brand_primary,
            'brand_secondary' => $this->ci2->brand_secondary,
            'link_color' => $this->ci2->link_color,
            'image' => null
        ]);

    }
}
