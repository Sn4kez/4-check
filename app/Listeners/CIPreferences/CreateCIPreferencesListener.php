<?php
namespace App\Listeners\CIPreferences;

use App\Events\Company\CompanyCreateEvent;
use App\CorporateIdentity;
use App\CorporateIdentityLogin;
use Ramsey\Uuid\Uuid;

class CreateCIPreferencesListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }
    /**
     * Handle the event.
     *
     * @param  UserCreateEvent  $event
     * @return void
     */
    public function handle(CompanyCreateEvent $event)
    {
        $ci = new CorporateIdentity();
        $ci->company()->associate($event->company);
        $ci->save();

        $brandedLogin = new CorporateIdentityLogin();
        $brandedLogin->id = $event->company->id;
        $brandedLogin->corporateIdentity()->associate($ci);
        $brandedLogin->save();
    }
}
