<?php

use App\Company;
use App\Gender;
use App\Locale;
use App\Role;
use App\User;
use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        /* @var \Illuminate\Database\Eloquent\Collection $users */
        $users = factory(User::class, 5)->make();

        $users = $users->concat(factory(User::class, 5)
            ->states([
                'with_first_name',
                'with_last_name',
            ])
            ->make());

        $users = $users->concat(factory(User::class, 5)
            ->states([
                'with_first_name',
                'with_middle_name',
                'with_last_name',
            ])
            ->make());

        $companies = Company::all();
        $genders = Gender::all();
        $roles = Role::all();
        $locales = Locale::all();
        $timezones = collect(DateTimeZone::listIdentifiers());

        $users->each(function (User $user) use ($companies, $genders, $locales, $roles, $timezones) {
            /** @var Company $company */
            $company = $companies->random();
            $user->company()->associate($company);
            $user->gender()->associate($genders->random());
            $user->role()->associate($roles->random());
            $user->locale()->associate($locales->random());
            $user->timezone = $timezones->random();
            $user->save();
            $groups = $company->groups;
            $num_groups = rand(0, min(2, $groups->count()));
            $user->groups()->attach($groups->random($num_groups));
        });
    }
}
