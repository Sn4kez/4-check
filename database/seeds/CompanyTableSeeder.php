<?php

use App\Address;
use App\AddressType;
use App\Company;
use App\Country;
use App\Directory;
use App\Sector;
use App\CompanySubscription;
use Illuminate\Database\Seeder;
use Ramsey\Uuid\Uuid;

class CompanyTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $companies = factory(Company::class, 5)->make();

        $sectors = Sector::all();

        $companies->each(function (Company $company) use ($sectors) {
            $company->id = Uuid::uuid4()->toString();

            if (rand(0, 4) != 0) {
                $company->sector()->associate($sectors->random());
            }
            $company->save();
            if(rand(0, 1) == 0) {
                $address = $this->makeFakeAddress(AddressType::find('billing'));
                $address->company()->associate($company);
                $address->save();
            }
            if(rand(0, 1) == 0) {
                $address = $this->makeFakeAddress(AddressType::find('postal'));
                $address->company()->associate($company);
                $address->save();
            }
        });
    }

    private function makeFakeAddress($type)
    {
        $states = array_rand([
            'with_line1' => null,
            'with_line2' => null,
            'with_city' => null,
            'with_postal_code' => null,
            'with_province' => null,
        ], rand(1, 5));
        /** @var Address $address */
        $address = factory(Address::class)->states($states)->make();
        $address->type()->associate($type);
        $address->country()->associate(Country::all()->random());
        return $address;
    }
}
