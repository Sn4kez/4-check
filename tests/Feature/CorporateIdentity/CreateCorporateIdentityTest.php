<?php

use App\Company;
use App\CorporateIdentity;
use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;

class CreateCorporateIdentityTest extends TestCase
{
    use DatabaseMigrations;

    public function provideInvalidAccessData()
    {
        return [
            [null, Response::HTTP_UNAUTHORIZED],
            [self::$USER, Response::HTTP_UNAUTHORIZED],
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
        $this->json('POST', '/companies/preferences/ci');
        $this->seeStatusCode($statusCode);
        $this->seeHeader('Content-Type', 'application/json');
    }

    public function provideValidAccessData()
    {
        return [
            [self::$ADMIN, 'logo.jpg'],
            [self::$SUPER_ADMIN, 'logo.jpg'],
            [self::$ADMIN, null],
            [self::$SUPER_ADMIN, null],
        ];
    }

    /**
     * @param string $userKey
     * @dataProvider provideValidAccessData
     */
    public function testValidAccess($userKey, $image)
    {
        $user = $this->getUser($userKey);
        $this->actingAs($user);

        $ci = new CorporateIdentity();
        $ci->brand_primary = '#000000';
        $ci->brand_secondary = '#FFFFFF';
        $ci->link_color = '#FF0000';
        $ci->company()->associate($this->company);

        $base64Source = null;

        if (!is_null($image)) {
            $fileLocation = sprintf("%s/files/%s", dirname(__FILE__), $image);
            $base64Source = base64_encode(file_get_contents($fileLocation));
        }

        $this->json('POST', '/companies/preferences/ci', [
            'company' => $ci->company->id,
            'brand_primary' => $ci->brand_primary,
            'brand_secondary' => $ci->brand_secondary,
            'link_color' => $ci->link_color,
            'source_b64' => $base64Source
        ]);

        $this->seeStatusCode(Response::HTTP_CREATED);
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
            'data' => array_merge([
                'id',
                'company',
                'brand_primary',
                'brand_secondary',
                'link_color'
            ], $image !== null ? ['image'] : []),
        ])->seeJsonContains([
            'company' => $ci->company->id,
            'brand_primary' => $ci->brand_primary,
            'brand_secondary' => $ci->brand_secondary,
            'link_color' => $ci->link_color
        ]);
    }

    public function provideInvalidEntities()
    {
        return [
            ['brand_primary', 123],
            ['brand_secondary', 123],
            ['link_color', 123],
        ];
    }

    /**
     * @param string $attribute
     * @param string $value
     * @dataProvider provideInvalidEntities
     */
    public function testInvalidEntities($attribute, $value)
    {
        $this->actingAs($this->user);

        $ci = new CorporateIdentity();
        $ci->brand_primary = '#000000';
        $ci->brand_secondary = '#FFFFFF';
        $ci->link_color = '#FF0000';
        $ci->company()->associate($this->company);

        $data = array_merge([
            'company' => $ci->company->id,
            'brand_primary' => $ci->brand_primary,
            'brand_secondary' => $ci->brand_secondary,
            'link_color' => $ci->link_color
        ], [$attribute => $value]);

        $this->json('POST', '/companies/preferences/ci', $data);

        $this->seeStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->seeHeader('Content-Type', 'application/json');
    }
}
