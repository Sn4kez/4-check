<?php

use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Ramsey\Uuid\Uuid;

class ViewCompanyReportSettingsTest extends TestCase
{
    use DatabaseMigrations;

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
            $uri = '/companies/' . $this->company->id . '/reportsettings';
        } else {
            $uri = '/companies/' . Uuid::uuid4()->toString() . '/reportsettings';
        }
        $this->json('GET', $uri);
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
     * @param $userKey
     * @dataProvider provideValidAccessData
     */
    public function testValidAccess($userKey)
    {
        $user = $this->getUser($userKey);
        $this->actingAs($user);
        $this->json('GET', '/companies/' . $this->company->id . '/reportsettings');
        $this->seeStatusCode(Response::HTTP_OK);
        $this->seeHeader('Content-Type', 'application/json');
        $this->seeJsonStructure([
            'data' => [
                'id',
                'showCompanyName',
                'showCompanyAddress',
                'showUsername',
                'showPageNumbers',
                'showExportDate',
                'showVersion',
                'text',
                'logoPosition',
            ],
        ])->seeJsonContains([
            'id' => $this->company->reportSettings->id,
            'showCompanyName' => $this->company->reportSettings->showCompanyName,
            'showCompanyAddress' => $this->company->reportSettings->showCompanyAddress,
            'showUsername' => $this->company->reportSettings->showUsername,
            'showPageNumbers' => $this->company->reportSettings->showPageNumbers,
            'showExportDate' => $this->company->reportSettings->showExportDate,
            'showVersion' => $this->company->reportSettings->showVersion,
            'text' => $this->company->reportSettings->text,
            'logoPosition' => $this->company->reportSettings->logoPosition,
        ]);
    }
}
