<?php

namespace App\Providers;

use App\AccessGrant;
use App\ArchiveDirectory;
use App\Checklist;
use App\ChecklistEntry;
use App\Check;
use App\Checkpoint;
use App\Company;
use App\Directory;
use App\Extension;
use App\Group;
use App\ScoringScheme;
use App\Section;
use App\User;
use App\LocationCheck;
use App\TextfieldCheck;
use App\ParticipantCheck;
use App\PictureCheck;
use App\ValueCheck;
use App\ChoiceCheck;
use DateTime;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Carbon;
use Illuminate\Support\ServiceProvider;
use Laravel\Cashier\Cashier;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        // Customize serialization of Carbon dates.
        Carbon::serializeUsing(function (Carbon $date) {
            return $date->format(DateTime::ATOM);
        });
        // Customize polymorphic types.
        $morph_map = [
            AccessGrant::SUBJECT_TYPE_USER => User::class,
            AccessGrant::SUBJECT_TYPE_GROUP => Group::class,
            AccessGrant::OBJECT_TYPE_DIRECTORY =>  Directory::class,
            AccessGrant::OBJECT_TYPE_CHECKLIST =>  Checklist::class,
            AccessGrant::OBJECT_TYPE_ARCHIVE_DIRECTORY =>  ArchiveDirectory::class,
            ScoringScheme::SCOPE_TYPE_COMPANY => Company::class,
            ScoringScheme::SCOPE_TYPE_CHECKLIST => Checklist::class,
            ChecklistEntry::PARENT_TYPE_CHECKLIST => Checklist::class,
            ChecklistEntry::PARENT_TYPE_SECTION => Section::class,
            ChecklistEntry::OBJECT_TYPE_SECTION => Section::class,
            ChecklistEntry::OBJECT_TYPE_CHECKPOINT => Checkpoint::class,
            ChecklistEntry::OBJECT_TYPE_EXTENSION => Extension::class,
            Check::VALUE_TYPE_VALUE => ValueCheck::class,
            Check::VALUE_TYPE_CHOICE => ChoiceCheck::class,
            Check::VALUE_TYPE_TEXTFIELD => TextfieldCheck::class,
            Check::VALUE_TYPE_PARTICIPANTS => ParticipantCheck::class,
            Check::VALUE_TYPE_PICTURE => PictureCheck::class,
            Check::VALUE_TYPE_LOCATION => LocationCheck::class,
            Checklist::APPROVER_TYPE_USER => User::class,
            Checklist::APPROVER_TYPE_GROUP => Group::class,
        ];
        foreach (array_keys(Extension::TYPES) as $type) {
            $morph_map[$type] = Extension::TYPES[$type]['model'];
        }
        Relation::morphMap($morph_map);

        /**
         * see
         * https://laravel.com/docs/5.7/billing#currency-configuration
         */
        Cashier::useCurrency('eur', '€');
    }
}
