<?php

namespace App\Providers;

use Laravel\Lumen\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'App\Events\Checklist\ChecklistCriticalRatingEvent' => [
            'App\Listeners\Checklist\CriticalRatingSendMailListener',
            //'App\Listeners\Checklist\CriticalRatingSendNotificationListener'
        ],
        'App\Events\Checklist\ChecklistDueEvent' => [
            'App\Listeners\Checklist\DueSendMailListener',
            //'App\Listeners\Checklist\DueSendNotificationLsitener'
        ],
        'App\Events\Checklist\ChecklistEscalationEvent' => [
            'App\Listeners\Checklist\EscalationSendMailListener',
            //'App\Listeners\Checklist\EscalationSendNotificationListener'
        ],
        'App\Events\Registration\NewUserInvitationEvent' => [
            'App\Listeners\Registration\UserInvitationSendMailListener'
        ],
        'App\Events\Task\TaskCompleteEvent' => [
            'App\Listeners\Task\TaskCompleteSendMailListener',
            'App\Listeners\Task\TaskCompleteSendNotificationListener'
        ],
        'App\Events\Task\TaskCreateEvent' => [
            'App\Listeners\Task\TaskCreateSendMailListener',
            'App\Listeners\Task\TaskCreateSendNotificationListener'
        ],
        'App\Events\Task\TaskDeleteEvent' => [
            'App\Listeners\Task\TaskDeleteSendMailListener',
            'App\Listeners\Task\TaskDeleteSendNotificationListener'
        ],
        'App\Events\Task\TaskOverdueEvent' => [
            'App\Listeners\Task\TaskOverdueSendMailListener',
            'App\Listeners\Task\TaskOverdueSendNotificationListener'
        ],
        'App\Events\Task\TaskUpdateEvent' => [
            'App\Listeners\Task\TaskUpdateSendMailListener',
            'App\Listeners\Task\TaskUpdateSendNotificationListener'
        ],
        'App\Events\Task\TaskAssignedEvent' => [
            'App\Listeners\Task\TaskAssignedSendNotificationListener'
        ],
        'App\Events\User\UserCreateEvent' => [
            'App\Listeners\NotificationPreferences\CreateNotificationPreferencesListener@onUserRegistration',
            'App\Listeners\Registration\UserRegistrationSendMailListener',
        ],
        'App\Events\User\InvitedUserRegistration' => [
            'App\Listeners\NotificationPreferences\CreateNotificationPreferencesListener@onUserInvitation',
        ],
        'App\Events\PasswordReset\PasswordResetTokenSetEvent' => [
            'App\Listeners\PasswordReset\SendResetLinkListener'
        ]
        ,
        [
            'App\Events\Company\CompanyCreateEvent' => [
                'App\Listeners\CIPreferences\CreateCIPreferencesListener'
            ]
        ],
        [
            'App\Events\Checklist\ChecklistEntryUpdatedEvent' => [
                'App\Listener\Checklist\UpdatedChecklistEntryListener'
            ]
        ],
        [
            'App\Events\Checklist\ChecklistUpdatedEvent' => [
                'App\Listener\Checklist\UpdatedChecklistListener'
            ]
        ],
        [
            'App\Events\Audit\AuditFinishedEvent' => [
                'App\Listener\Audit\FinishedAuditListener'
            ]
        ],
    ];
}
