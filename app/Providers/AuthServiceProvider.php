<?php

namespace App\Providers;


use App\Payment;
use App\ArchiveDirectory;
use App\Checkpoint;
use App\ChoiceScheme;
use App\Company;
use App\Checklist;
use App\Dashboard;
use App\Directory;
use App\LocationExtension;
use App\PictureExtension;
use App\Policies\DashboardPolicy;
use App\UserInvitation;
use App\ParticipantExtension;
use App\Policies\ChecklistPolicy;
use App\Policies\CheckpointPolicy;
use App\Policies\ChoiceSchemePolicy;
use App\Policies\CompanyPolicy;
use App\Policies\DirectoryPolicy;
use App\Policies\LocationExtensionPolicy;
use App\Policies\ParticipantExtensionPolicy;
use App\Policies\PaymentPolicy;
use App\Policies\PictureExtensionPolicy;
use App\Policies\ReportSettingsPolicy;
use App\Policies\ScoreConditionPolicy;
use App\Policies\ScorePolicy;
use App\Policies\ScoringSchemePolicy;
use App\Policies\SectionPolicy;
use App\Policies\CompanySubscriptionPolicy;
use App\Policies\TextfieldExtensionPolicy;
use App\Policies\UserPolicy;
use App\Policies\TaskPolicy;
use App\Policies\TaskStatePolicy;
use App\Policies\TaskTypePolicy;
use App\Policies\TaskPriorityPolicy;
use App\Policies\LocationPolicy;
use App\Policies\LocationStatePolicy;
use App\Policies\LocationTypePolicy;
use App\Policies\ValueSchemePolicy;
use App\Policies\NotificationPolicy;
use App\Policies\NotificationPreferencesPolicy;
use App\Policies\CorporateIdentityPolicy;
use App\Policies\CorporateIdentityLoginPolicy;
use App\Policies\UserInvitationPolicy;
use App\Policies\AuditPolicy;
use App\Policies\ArchiveDirectoryPolicy;
use App\Policies\InspectionPlanPolicy;
use App\ReportSettings;
use App\Score;
use App\ScoreCondition;
use App\ScoringScheme;
use App\Section;
use App\CompanySubscription;
use App\TextfieldExtension;
use App\User;
use App\Task;
use App\TaskState;
use App\TaskType;
use App\TaskPriority;
use App\Location;
use App\LocationType;
use App\LocationState;
use App\Notification;
use App\ValueScheme;
use App\NotificationPreferences;
use App\CorporateIdentity;
use App\CorporateIdentityLogin;
use App\Audit;
use App\InspectionPlan;

use Dusterio\LumenPassport\LumenPassport;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;

class AuthServiceProvider extends ServiceProvider
{

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        // Ignore Passport's migrations, since we use customized migrations
        // for setting up the necessary tables
        Passport::ignoreMigrations();
    }

    /**
     * Boot the authentication services for the application.
     *
     * @return void
     */
    public function boot()
    {
        // Setup Passport's routes
        LumenPassport::routes($this->app, [
            'prefix' => config('app.api_prefix') . '/auth',
        ]);
        // Setup the authorization policies
        app(Gate::class)::policy(Company::class, CompanyPolicy::class);
        app(Gate::class)::policy(CompanySubscription::class, CompanySubscriptionPolicy::class);
        app(Gate::class)::policy(User::class, UserPolicy::class);
        app(Gate::class)::policy(UserInvitation::class, UserInvitationPolicy::class);
        app(Gate::class)::policy(Audit::class, AuditPolicy::class);
        app(Gate::class)::policy(Task::class, TaskPolicy::class);
        app(Gate::class)::policy(TaskState::class, TaskStatePolicy::class);
        app(Gate::class)::policy(TaskType::class, TaskTypePolicy::class);
        app(Gate::class)::policy(TaskPriority::class, TaskPriorityPolicy::class);
        app(Gate::class)::policy(Location::class, LocationPolicy::class);
        app(Gate::class)::policy(LocationState::class, LocationStatePolicy::class);
        app(Gate::class)::policy(LocationType::class, LocationTypePolicy::class);
        app(Gate::class)::policy(Directory::class, DirectoryPolicy::class);
        app(Gate::class)::policy(Checklist::class, ChecklistPolicy::class);
        app(Gate::class)::policy(Section::class, SectionPolicy::class);
        app(Gate::class)::policy(ScoringScheme::class, ScoringSchemePolicy::class);
        app(Gate::class)::policy(Notification::class, NotificationPolicy::class);
        app(Gate::class)::policy(Score::class, ScorePolicy::class);
        app(Gate::class)::policy(Checkpoint::class, CheckpointPolicy::class);
        app(Gate::class)::policy(ChoiceScheme::class, ChoiceSchemePolicy::class);
        app(Gate::class)::policy(ValueScheme::class, ValueSchemePolicy::class);
        app(Gate::class)::policy(ScoreCondition::class, ScoreConditionPolicy::class);
        app(Gate::class)::policy(TextfieldExtension::class, TextfieldExtensionPolicy::class);
        app(Gate::class)::policy(LocationExtension::class, LocationExtensionPolicy::class);
        app(Gate::class)::policy(ParticipantExtension::class, ParticipantExtensionPolicy::class);
        app(Gate::class)::policy(NotificationPreferences::class, NotificationPreferencesPolicy::class);
        app(Gate::class)::policy(CorporateIdentity::class, CorporateIdentityPolicy::class);
        app(Gate::class)::policy(ReportSettings::class, ReportSettingsPolicy::class);
        app(Gate::class)::policy(PictureExtension::class, PictureExtensionPolicy::class);
        app(Gate::class)::policy(CorporateIdentityLogin::class, CorporateIdentityLoginPolicy::class);
        app(Gate::class)::policy(ArchiveDirectory::class, ArchiveDirectoryPolicy::class);
        app(Gate::class)::policy(Payment::class, PaymentPolicy::class);
        app(Gate::class)::policy(Dashboard::class, DashboardPolicy::class);
        app(Gate::class)::policy(InspectionPlan::class, InspectionPlanPolicy::class);
    }
}
