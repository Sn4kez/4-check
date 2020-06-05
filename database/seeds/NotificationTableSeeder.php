<?php

use Illuminate\Database\Seeder;

use App\Notification;

class NotificationTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(Notification::class, 100)->make()->each(function($notification) {
            $notification->save();
        });
    }
}
