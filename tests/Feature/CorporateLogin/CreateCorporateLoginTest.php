<?php

use App\CorporateIdentity;
use App\CorporateIdentityLogin;
use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Ramsey\Uuid\Uuid;

class CreateCorporateLoginTest extends TestCase
{
	use DatabaseMigrations;

    public function testValidAccess()
    {

        $ci = new CorporateIdentity();
        $ci->id = Uuid::uuid4()->toString();
        $ci->brand_primary = '#000000';
        $ci->brand_secondary = '#FFFFFF';
        $ci->link_color = '#FF0000';
        $ci->companyId = $this->company->id;
        $ci->save();

        $id = 'test';

        $this->json('POST', '/login/ci', [
            'id' => $id,
            'company' => $this->company->id,
        ]);

        $this->seeStatusCode(Response::HTTP_CREATED);
        $this->seeHeader('Content-Type', 'application/json');

        $this->seeJsonStructure([
            'data' => [
                'id',
                'ci',
            ],
        ])->seeJsonNotNull([
            'data' => [
                'id',
                'ci'
            ],
        ])->seeJsonContains([
            'id' => $id,
            'ci' => $ci->id
        ]);
    }

    public function provideInvalidEntities()
    {
        return [
            [123],
            [null],
        ];
    }

    /**
     * @param string $value
     * @dataProvider provideInvalidEntities
     */
    public function testInvalidEntities($value)
    {

        $ci = new CorporateIdentity();
        $ci->brand_primary = '#000000';
        $ci->brand_secondary = '#FFFFFF';
        $ci->link_color = '#FF0000';
        $ci->company()->associate($this->company);
        $ci->save();

        $this->json('POST', '/login/ci', [
            'id' => $value,
            'ci' => $ci->id,
        ]);

        $this->seeStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->seeHeader('Content-Type', 'application/json');
    }
}
