<?php

use App\Phone;
use App\PhoneType;
use App\User;
use Illuminate\Database\Seeder;

class PhonesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        /* @var \Illuminate\Database\Eloquent\Collection $phones */
        $phones = factory(Phone::class, 25)->make();

        $users = User::all();
        $types = PhoneType::all();

        $phones->each(function (Phone $phone) use ($users, $types) {
            $phone->user()->associate($users->random());
            $phone->type()->associate($types->random());
            $phone->save();
        });
    }
}
