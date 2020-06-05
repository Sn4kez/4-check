<?php

use App\Company;
use App\Group;
use Illuminate\Database\Seeder;

class GroupsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        /** @var \Illuminate\Database\Eloquent\Collection $groups */
        $groups = factory(Group::class, 25)->make();

        $companies = Company::all();

        $groups->each(function (Group $group) use ($companies) {
            $group->company()->associate($companies->random());
            $group->save();
        });
    }
}
