<?php

use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Ramsey\Uuid\Uuid;

class UpdateReportSettingsTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * @var \App\Company $company
     */
    protected $company;

    public function provideInvalidAccessData()
    {
        return [
            [null, 'random', Response::HTTP_UNAUTHORIZED],
            [null, 'company', Response::HTTP_UNAUTHORIZED],
            [self::$USER, 'random', Response::HTTP_NOT_FOUND],
            [self::$USER, 'company', Response::HTTP_FORBIDDEN],
            [self::$ADMIN, 'random', Response::HTTP_NOT_FOUND],
            [self::$OTHER_ADMIN, 'company', Response::HTTP_FORBIDDEN],
        ];
    }

    /**
     * @param string $userKey
     * @param boolean $addressKey
     * @param int $statusCode
     * @dataProvider provideInvalidAccessData
     */
    public function testInvalidAccess($userKey, $addressKey, $statusCode)
    {
        if ($userKey != null) {
            $user = $this->getUser($userKey);
            $this->actingAs($user);
        }
        if ($addressKey === 'company') {
            $uri = '/reportsettings/' . $this->company->reportSettings->id;
        } else {
            $uri = '/reportsettings/' . Uuid::uuid4()->toString();
        }
        $this->json('PATCH', $uri);
        $this->seeStatusCode($statusCode);
        $this->seeHeader('Content-Type', 'application/json');
    }

    public function provideValidEntities()
    {
        return [
            [self::$ADMIN, 'showCompanyName', 'showCompanyName', false],
            [self::$SUPER_ADMIN, 'showCompanyName', 'showCompanyName', true],
            [self::$ADMIN, 'showCompanyAddress', 'showCompanyAddress', false],
            [self::$SUPER_ADMIN, 'showCompanyAddress', 'showCompanyAddress', true],
            [self::$ADMIN, 'showUsername', 'showUsername', false],
            [self::$SUPER_ADMIN, 'showUsername', 'showUsername', true],
            [self::$ADMIN, 'showPageNumbers', 'showPageNumbers', false],
            [self::$SUPER_ADMIN, 'showPageNumbers', 'showPageNumbers', true],
            [self::$ADMIN, 'showExportDate', 'showExportDate', false],
            [self::$SUPER_ADMIN, 'showExportDate', 'showExportDate', true],
            [self::$ADMIN, 'showVersion', 'showVersion', false],
            [self::$SUPER_ADMIN, 'showVersion', 'showVersion', true],
            [self::$ADMIN, 'text', 'text', 'text'],
            [self::$SUPER_ADMIN, 'text', 'text', 'text'],
            [self::$ADMIN, 'logoPosition', 'logoPosition', 'left'],
            [self::$SUPER_ADMIN, 'logoPosition', 'logoPosition', 'left'],
            [self::$ADMIN, 'logoPosition', 'logoPosition', 'right'],
            [self::$SUPER_ADMIN, 'logoPosition', 'logoPosition', 'right'],
            [self::$ADMIN, 'logoPosition', 'logoPosition', 'center'],
            [self::$SUPER_ADMIN, 'logoPosition', 'logoPosition', 'center'],
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
        $data = [$attribute => $value];
        $url = '/reportsettings/' . $this->company->reportSettings->id;
        $this->json('PATCH', $url, $data);
        $this->seeStatusCode(Response::HTTP_NO_CONTENT);
        $this->seeInDatabase('report_settings', [
            'id' => $this->company->reportSettings->id,
            $dbAttribute => $value,
        ]);
    }

    public function provideInvalidEntities()
    {
        return [
            [self::$ADMIN, 'showCompanyName', null],
            [self::$ADMIN, 'showCompanyName', 123],
            [self::$ADMIN, 'showCompanyAddress', null],
            [self::$ADMIN, 'showCompanyAddress', 123],
            [self::$ADMIN, 'showUsername', null],
            [self::$ADMIN, 'showUsername', 123],
            [self::$ADMIN, 'showPageNumbers', null],
            [self::$ADMIN, 'showPageNumbers', 123],
            [self::$ADMIN, 'showExportDate', null],
            [self::$ADMIN, 'showExportDate', 123],
            [self::$ADMIN, 'showVersion', null],
            [self::$ADMIN, 'showVersion', 123],
            [self::$ADMIN, 'text', 123],
        ];
    }

    /**
     * @param $userKey
     * @param $attribute
     * @param $value
     * @dataProvider provideInvalidEntities
     */
    public function testInvalidEntities($userKey, $attribute, $value)
    {
        $user = $this->getUser($userKey);
        $this->actingAs($user);
        $data = [$attribute => $value];
        $url = '/reportsettings/' . $this->company->reportSettings->id;
        $this->json('PATCH', $url, $data);
        $this->seeStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->seeHeader('Content-Type', 'application/json');
    }
}
