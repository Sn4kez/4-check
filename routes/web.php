<?php

use Illuminate\Http\Response;

/** @var \Laravel\Lumen\Routing\Router $router */
$router->group(['prefix' => config('app.api_prefix')], function () use ($router) {

    // Unauthenticated routes
    $router->group(['prefix' => 'users'], function () use ($router) {
        $router->post('/', ['uses' => 'UserController@create']);

        $router->group(['prefix' => 'verify'], function () use ($router) {
            $router->post('/', ['uses' => 'UserController@verifyEmail']);
        });

        //passwords
        $router->group(['prefix' => '/password'], function () use ($router) {
            $router->post('/token', ['uses' => 'UserController@setResetPasswordToken']);
            $router->post('/reset', ['uses' => 'UserController@resetPassword']);
        });
    });

    $router->group(['prefix' => 'invitation'], function () use ($router) {
        $router->get('/{token}', ['uses' => 'UserInvitationController@provideData']);
        $router->post('/{token}', ['uses' => 'UserInvitationController@registerUser']);
    });

    $router->group(['prefix' => 'login'], function () use ($router) {
        //$router->post('/', ['uses' => 'LoginController@post']);
        $router->post('/', ['uses' => 'LoginController@post']);

        //ci for branded login
        $router->post('/ci', ['uses' => 'CorporateIdentityLoginController@create']);
        $router->get('/ci/{id}', ['uses' => 'CorporateIdentityLoginController@view']);
    });

    $router->group(['prefix' => 'test'], function () use ($router) {
        $router->post('/', ['uses' => 'LoginController@post']);
    });

    // Authenticated routes
    $router->group(['middleware' => 'auth:api'], function () use ($router) {
        // Users
        $router->group(['prefix' => 'users'], function () use ($router) {
            $router->get('/', ['uses' => 'UserController@index']);
            $router->get('/me', ['uses' => 'UserController@viewMe']);
            $router->get('/{id}', ['uses' => 'UserController@view']);
            $router->patch('/{id}', ['uses' => 'UserController@update']);
            $router->delete('/{id}', ['uses' => 'UserController@delete']);

            // Phones
            $router->group(['prefix' => '/{userId}/phones'], function () use ($router) {
                $router->post('/', ['uses' => 'PhoneController@create']);
                $router->get('/', ['uses' => 'PhoneController@index']);
            });

            // Groups
            $router->group(['prefix' => '/{userId}/groups'], function () use ($router) {
                $router->get('/', ['uses' => 'UserGroupController@indexUser']);
            });

            // Access Grants
            $router->group(['prefix' => '/{subjectId}/grants'], function () use ($router) {
                $router->get('/', ['uses' => 'AccessGrantController@indexSubject']);
            });

            // Preferences
            $router->group(['prefix' => '/preferences'], function () use ($router) {

                //notification preferences
                $router->group(['prefix' => '/notifications'], function () use ($router) {
                    $router->get('/[{userId}]', ['uses' => 'NotificationPreferencesController@view']);
                    $router->patch('/{userId}', ['uses' => 'NotificationPreferencesController@update']);
                });
            });

            //invites
            $router->group(['prefix' => '/invitations'], function () use ($router) {
                $router->post('/', 'UserInvitationController@create');
                $router->get('/company/{company}', ['uses' => 'UserInvitationController@index']);
                $router->get('/{token}', ['uses' => 'UserInvitationController@view']);
                $router->patch('/{token}', ['uses' => 'UserInvitationController@update']);
                $router->delete('/{token}', ['uses' => 'UserInvitationController@delete']);
            });
        });

        // Phones
        $router->group(['prefix' => 'phones'], function () use ($router) {
            $router->get('/{phoneId}', ['uses' => 'PhoneController@view']);
            $router->patch('/{phoneId}', ['uses' => 'PhoneController@update']);
            $router->delete('/{phoneId}', ['uses' => 'PhoneController@delete']);
        });

        // Companies
        $router->group(['prefix' => 'companies'], function () use ($router) {
            $router->get('/', ['uses' => 'CompanyController@index']);
            $router->get('/{companyId}', ['uses' => 'CompanyController@view']);
            $router->patch('/{companyId}', ['uses' => 'CompanyController@update']);
            $router->delete('/{companyId}', ['uses' => 'CompanyController@delete']);

            // Preferences
            $router->group(['prefix' => '/preferences'], function () use ($router) {
                $router->group(['prefix' => '/ci'], function () use ($router) {
                    $router->post('/', ['uses' => 'CorporateIdentityController@create']);
                    $router->get('/{companyId}', ['uses' => 'CorporateIdentityController@view']);
                    $router->patch('/{companyId}', ['uses' => 'CorporateIdentityController@update']);
                    $router->delete('/{companyId}', ['uses' => 'CorporateIdentityController@delete']);
                });
                $router->group(['prefix' => '/login'], function () use ($router) {
                    $router->patch('/{id}', ['uses' => 'CorporateIdentityLoginController@update']);
                    $router->delete('/{id}', ['uses' => 'CorporateIdentityLoginController@delete']);
                });
            });

            // Addresses
            $router->group(['prefix' => '/{companyId}/addresses'], function () use ($router) {
                $router->post('/', ['uses' => 'AddressController@create']);
                $router->get('/', ['uses' => 'AddressController@index']);
            });

            // Groups
            $router->group(['prefix' => '/{companyId}/groups'], function () use ($router) {
                $router->post('/', ['uses' => 'GroupController@create']);
                $router->get('/', ['uses' => 'GroupController@index']);
            });

            // Scoring Schemes
            $router->group(['prefix' => '/{scopeId}/scoringschemes'], function () use ($router) {
                $router->post('/', ['uses' => 'ScoringSchemeController@create']);
                $router->get('/', ['uses' => 'ScoringSchemeController@indexScope']);
            });

            // Report Settings
            $router->group(['prefix' => '/{companyId}/reportsettings'], function () use ($router) {
                $router->get('/', ['uses' => 'ReportSettingsController@viewCompany']);
                $router->patch('/', ['uses' => 'ReportSettingsController@updateCompany']);
            });

            $router->group(['prefix' => '/{companyId}/users'], function () use ($router) {
                $router->get('/', ['uses' => 'CompanyController@indexUser']);
            });

            $router->group(['prefix' => '/{companyId}/subscription'], function () use ($router) {
                $router->get('/', ['uses' => 'CompanyController@subscription']);
            });
        });

        // Addresses
        $router->group(['prefix' => '/addresses'], function () use ($router) {
            $router->get('/{addressId}', ['uses' => 'AddressController@view']);
            $router->patch('/{addressId}', ['uses' => 'AddressController@update']);
            $router->delete('/{addressId}', ['uses' => 'AddressController@delete']);
        });

        // Report Settings
        $router->group(['prefix' => '/reportsettings'], function () use ($router) {
            $router->get('/{settingId}', ['uses' => 'ReportSettingsController@view']);
            $router->patch('/{settingId}', ['uses' => 'ReportSettingsController@update']);
        });

        // Groups
        $router->group(['prefix' => '/groups'], function () use ($router) {
            $router->get('/{groupId}', ['uses' => 'GroupController@view']);
            $router->patch('/{groupId}', ['uses' => 'GroupController@update']);
            $router->delete('/{groupId}', ['uses' => 'GroupController@delete']);

            // Members
            $router->group(['prefix' => '/{userId}/members'], function () use ($router) {
                $router->get('/', ['uses' => 'UserGroupController@indexGroup']);
                $router->patch('/', ['uses' => 'UserGroupController@update']);
                $router->delete('/', ['uses' => 'UserGroupController@delete']);
            });

            // Access Grants
            $router->group(['prefix' => '/{subjectId}/grants'], function () use ($router) {
                $router->get('/', ['uses' => 'AccessGrantController@indexSubject']);
            });
        });

        // Subscriptions
        $router->group(['prefix' => 'subscriptions'], function () use ($router) {
            $router->get('/{subscriptionId}', ['uses' => 'CompanySubscriptionController@view']);
            $router->patch('/{subscriptionId}', ['uses' => 'CompanySubscriptionController@update']);
        });

        // Tasks
        $router->group(['prefix' => 'tasks'], function () use ($router) {
            $router->get('/company/{companyId}', ['uses' => 'TaskController@index']);
            $router->get('/company', ['uses' => 'TaskController@index']);
            $router->post('/', ['uses' => 'TaskController@create']);
            $router->get('/{taskId}', ['uses' => 'TaskController@view']);
            $router->patch('/delete', ['uses' => 'TaskController@deleteSet']);
            $router->patch('/finish', ['uses' => 'TaskController@setSetDone']);
            $router->patch('/{taskId}', ['uses' => 'TaskController@update']);
            $router->delete('/{taskId}', ['uses' => 'TaskController@delete']);

            // Task Types
            $router->group(['prefix' => 'types'], function () use ($router) {
                $router->post('/', ['uses' => 'TaskTypeController@create']);
                $router->get('/company/{companyId}', ['uses' => 'TaskTypeController@index']);
                $router->get('/{locationTypeId}', ['uses' => 'TaskTypeController@view']);
                $router->patch('/{locationTypeId}', ['uses' => 'TaskTypeController@update']);
                $router->delete('/{locationTypeId}', ['uses' => 'TaskTypeController@delete']);
            });

            // Task Priorities
            $router->group(['prefix' => 'priorities'], function () use ($router) {
                $router->post('/', ['uses' => 'TaskPriorityController@create']);
                $router->get('/company/{companyId}', ['uses' => 'TaskPriorityController@index']);
                $router->get('/{locationPriorityId}', ['uses' => 'TaskPriorityController@view']);
                $router->patch('/{locationPriorityId}', ['uses' => 'TaskPriorityController@update']);
                $router->delete('/{locationPriorityId}', ['uses' => 'TaskPriorityController@delete']);
            });

            // Task States
            $router->group(['prefix' => 'states'], function () use ($router) {
                $router->post('/', ['uses' => 'TaskStateController@create']);
                $router->get('/company/{companyId}', ['uses' => 'TaskStateController@index']);
                $router->get('/{taskStateId}', ['uses' => 'TaskStateController@view']);
                $router->patch('/{taskStateId}', ['uses' => 'TaskStateController@update']);
                $router->delete('/{taskStateId}', ['uses' => 'TaskStateController@delete']);
            });
        });

        // Locations
        $router->group(['prefix' => 'locations'], function () use ($router) {
            $router->get('/company/{companyId}', ['uses' => 'LocationController@index']);
            $router->post('/', ['uses' => 'LocationController@create']);
            $router->get('/{locationId}', ['uses' => 'LocationController@view']);
            $router->patch('/delete', ['uses' => 'LocationController@deleteSet']);
            $router->patch('/{locationId}', ['uses' => 'LocationController@update']);
            $router->delete('/{locationId}', ['uses' => 'LocationController@delete']);

            // Location Types
            $router->group(['prefix' => 'types'], function () use ($router) {
                $router->post('/', ['uses' => 'LocationTypeController@create']);
                $router->get('/company/{companyId}', ['uses' => 'LocationTypeController@index']);
                $router->get('/{locationTypeId}', ['uses' => 'LocationTypeController@view']);
                $router->patch('/{locationTypeId}', ['uses' => 'LocationTypeController@update']);
                $router->delete('/{locationTypeId}', ['uses' => 'LocationTypeController@delete']);
            });

            // Location States
            $router->group(['prefix' => 'states'], function () use ($router) {
                $router->post('/', ['uses' => 'LocationStateController@create']);
                $router->get('/company/{companyId}', ['uses' => 'LocationStateController@index']);
                $router->get('/{locationStateId}', ['uses' => 'LocationStateController@view']);
                $router->patch('/{locationStateId}', ['uses' => 'LocationStateController@update']);
                $router->delete('/{locationStateId}', ['uses' => 'LocationStateController@delete']);
            });
        });

        // Directories
        $router->group(['prefix' => 'directories'], function () use ($router) {
            $router->post('/', ['uses' => 'DirectoryController@create']);
            $router->delete('/', ['uses' => 'DirectoryController@deleteSet']);
            $router->patch('/move', ['uses' => 'DirectoryEntryController@moveSet']);
            $router->patch('/copy', ['uses' => 'DirectoryEntryController@copySet']);
            $router->patch('/archive', ['uses' => 'ArchiveController@archiveSet']);
            $router->patch('/restore', ['uses' => 'RestoreArchiveController@restoreSet']);
            $router->get('/{directoryId}/entries', ['uses' => 'DirectoryController@index']);
            $router->patch('/{directoryId}/move', ['uses' => 'DirectoryEntryController@move']);
            $router->patch('/{directoryId}/copy', ['uses' => 'DirectoryEntryController@copy']);
            $router->patch('/{entryId}/archive', ['uses' => 'ArchiveController@archive']);
            $router->patch('/{entryId}/restore', ['uses' => 'RestoreArchiveController@restore']);
            $router->get('/{directoryId}', ['uses' => 'DirectoryController@view']);
            $router->patch('/{directoryId}', ['uses' => 'DirectoryController@update']);
            $router->delete('/{directoryId}', ['uses' => 'DirectoryController@delete']);

            // Access Grants
            $router->group(['prefix' => '/{objectId}/grants'], function () use ($router) {
                $router->post('/', ['uses' => 'AccessGrantController@create']);
                $router->get('/', ['uses' => 'AccessGrantController@indexObject']);
            });
        });// Archive
        $router->group(['prefix' => 'archives'], function () use ($router) {
            $router->post('/', ['uses' => 'ArchiveDirectoryController@create']);
            $router->delete('/', ['uses' => 'ArchiveDirectoryController@deleteSet']);
            $router->get('/{directoryId}/entries', ['uses' => 'ArchiveDirectoryController@index']);
            $router->get('/{directoryId}', ['uses' => 'ArchiveDirectoryController@view']);
            $router->patch('/{directoryId}', ['uses' => 'ArchiveDirectoryController@update']);
            $router->delete('/{directoryId}', ['uses' => 'ArchiveDirectoryController@delete']);
        });

        // Access Grants
        $router->group(['prefix' => 'grants'], function () use ($router) {
            $router->get('/{grantId}', ['uses' => 'AccessGrantController@view']);
            $router->patch('/{grantId}', ['uses' => 'AccessGrantController@update']);
            $router->delete('/{grantId}', ['uses' => 'AccessGrantController@delete']);
        });

        $router->group(['prefix' => 'audits'], function () use ($router) {
            $router->post('/', ['uses' => 'AuditController@create']);
            $router->get('/company/{companyId}', ['uses' => 'AuditController@index']);
            $router->get('/archive/company/{companyId}', ['uses' => 'AuditController@indexArchive']);
            $router->patch('/archive', ['uses' => 'AuditController@archiveSet']);
            $router->patch('/restore', ['uses' => 'AuditController@restoreSet']);
            $router->patch('/archive/{auditId}', ['uses' => 'AuditController@archive']);
            $router->patch('/restore/{auditId}', ['uses' => 'AuditController@restore']);
            $router->get('/start/{auditId}', ['uses' => 'CheckController@startAudit']);
            $router->get('/entries/{auditId}', ['uses' => 'CheckController@entries']);
            $router->get('/export', ['uses' => 'AuditExportController@index']);

            $router->group(['prefix' => 'states'], function () use ($router) {
                $router->post('/', ['uses' => 'AuditStateController@create']);
                $router->get('/company/{companyId}', ['uses' => 'AuditStateController@index']);
                $router->get('/{auditStateId}', ['uses' => 'AuditStateController@view']);
                $router->patch('/{auditStateId}', ['uses' => 'AuditStateController@update']);
                $router->delete('/{auditStateId}', ['uses' => 'AuditStateController@delete']);
            });


            $router->group(['prefix' => 'checks'], function () use ($router) {
                $router->get('/{checkId}', ['uses' => 'CheckController@view']);
                $router->patch('/{checkId}', ['uses' => 'CheckController@update']);
                $router->delete('/{checkId}', ['uses' => 'CheckController@delete']);
            });

            $router->group(['prefix' => 'directory'], function () use ($router) {
                $router->get('/{entryId}', ['uses' => 'AuditController@getExecutedAudits']);
                $router->get('/{entryId}/results', ['uses' => 'AuditController@getExecutedAuditsResults']);
            });

            $router->group(['prefix' => 'plans'], function () use ($router) {
                $router->post('/', ['uses' => 'InspectionPlanController@create']);
                $router->get('/', ['uses' => 'InspectionPlanController@index']);
                $router->get('/company/{companyId}', ['uses' => 'InspectionPlanController@index']);
                $router->patch('/{planId}', ['uses' => 'InspectionPlanController@update']);
                $router->delete('/{planId}', ['uses' => 'InspectionPlanController@delete']);
                $router->get('/{planId}', ['uses' => 'InspectionPlanController@view']);

            });

            $router->get('/{auditId}', ['uses' => 'AuditController@view']);
            $router->patch('/{auditId}', ['uses' => 'AuditController@update']);
            $router->delete('/{auditId}', ['uses' => 'AuditController@delete']);
        });

        // Checklists
        $router->group(['prefix' => 'checklists'], function () use ($router) {
            $router->post('/', ['uses' => 'ChecklistController@create']);
            $router->delete('/', ['uses' => 'ChecklistController@deleteSet']);
            $router->get('/{checklistId}', ['uses' => 'ChecklistController@view']);
            $router->patch('/{checklistId}', ['uses' => 'ChecklistController@update']);
            $router->delete('/{checklistId}', ['uses' => 'ChecklistController@delete']);

            // Access Grants
            $router->group(['prefix' => '/{objectId}/grants'], function () use ($router) {
                $router->post('/', ['uses' => 'AccessGrantController@create']);
                $router->get('/', ['uses' => 'AccessGrantController@indexObject']);
            });

            // Entries
            $router->group(['prefix' => '/{checklistId}/entries'], function () use ($router) {
                $router->get('/', ['uses' => 'ChecklistController@indexEntries']);
            });

            // Sections
            $router->group(['prefix' => '/{checklistId}/sections'], function () use ($router) {
                $router->post('/', ['uses' => 'SectionController@create']);
                $router->get('/', ['uses' => 'SectionController@indexSections']);
            });

            // Checkpoints
            $router->group(['prefix' => '/{checklistId}/checkpoints'], function () use ($router) {
                $router->post('/', ['uses' => 'CheckpointController@create']);
                $router->get('/', ['uses' => 'CheckpointController@indexCheckpoints']);
            });

            // Extensions
            $router->group(['prefix' => '/{checklistId}/extensions'], function () use ($router) {
                $router->post('/', ['uses' => 'ExtensionController@create']);
                $router->get('/', ['uses' => 'ExtensionController@indexExtensions']);
            });

            // Scoring Schemes
            $router->group(['prefix' => '/{scopeId}/scoringschemes'], function () use ($router) {
                $router->post('/', ['uses' => 'ScoringSchemeController@create']);
                $router->get('/', ['uses' => 'ScoringSchemeController@indexScope']);
            });

            //approver 
            $router->group(['prefix' => '/{checklistId}/approvers'], function () use ($router) {
                $router->get('/', ['uses' => 'ChecklistApprovalUserController@index']);
                $router->patch('/', ['uses' => 'ChecklistApprovalUserController@multiAdd']);
                $router->delete('/', ['uses' => 'ChecklistApprovalUserController@multiRemove']);
                $router->patch('/{userId}', ['uses' => 'ChecklistApprovalUserController@add']);
                $router->patch('/{userId}', ['uses' => 'ChecklistApprovalUserController@add']);
                $router->delete('/{userId}', ['uses' => 'ChecklistApprovalUserController@remove']);

                $router->group(['prefix' => '/group/{groupId}'], function () use ($router) {
                    $router->patch('/', ['uses' => 'ChecklistApprovalUserController@addGroup']);
                    $router->delete('/', ['uses' => 'ChecklistApprovalUserController@removeGroup']);
                });
            });
        });

        // Sections
        $router->group(['prefix' => '/sections'], function () use ($router) {
            $router->get('/{sectionId}', ['uses' => 'SectionController@view']);
            $router->patch('/{sectionId}', ['uses' => 'SectionController@update']);
            $router->delete('/{sectionId}', ['uses' => 'SectionController@delete']);

            // Entries
            $router->group(['prefix' => '/{checklistId}/entries'], function () use ($router) {
                $router->get('/', ['uses' => 'SectionController@indexEntries']);
            });

            // Checkpoints
            $router->group(['prefix' => '/{sectionId}/checkpoints'], function () use ($router) {
                $router->post('/', ['uses' => 'CheckpointController@create']);
                $router->get('/', ['uses' => 'CheckpointController@indexCheckpoints']);
            });

            // Extensions
            $router->group(['prefix' => '/{sectionId}/extensions'], function () use ($router) {
                $router->post('/', ['uses' => 'ExtensionController@create']);
                $router->get('/', ['uses' => 'ExtensionController@indexExtensions']);
            });
        });

        // Scoring Schemes
        $router->group(['prefix' => '/scoringschemes'], function () use ($router) {
            $router->get('/{schemeId}', ['uses' => 'ScoringSchemeController@view']);
            $router->patch('/{schemeId}', ['uses' => 'ScoringSchemeController@update']);
            $router->delete('/{schemeId}', ['uses' => 'ScoringSchemeController@delete']);

            // Scores
            $router->group(['prefix' => '/{schemeId}/scores'], function () use ($router) {
                $router->post('/', ['uses' => 'ScoreController@create']);
                $router->get('/', ['uses' => 'ScoreController@index']);
            });
        });

        // Scores
        $router->group(['prefix' => '/scores'], function () use ($router) {
            $router->get('/{scoreId}/notice/{checklistId}', ['uses' => 'ScoreNotificationController@index']);
            $router->post('/{scoreId}/notice', ['uses' => 'ScoreNotificationController@create']);
            $router->delete('/{scoreNotificationId}/notice', ['uses' => 'ScoreNotificationController@delete']);
            $router->get('/{scoreId}', ['uses' => 'ScoreController@view']);
            $router->patch('/{scoreId}', ['uses' => 'ScoreController@update']);
            $router->delete('/{scoreId}', ['uses' => 'ScoreController@delete']);
        });

        // Checkpoints
        $router->group(['prefix' => '/checkpoints'], function () use ($router) {
            $router->get('/{checkpointId}', ['uses' => 'CheckpointController@view']);
            $router->patch('/{checkpointId}', ['uses' => 'CheckpointController@update']);
            $router->delete('/{checkpointId}', ['uses' => 'CheckpointController@delete']);

            // Entries
            $router->group(['prefix' => '/{checklistId}/entries'], function () use ($router) {
                $router->get('/', ['uses' => 'CheckpointController@indexEntries']);
            });

            // Extensions
            $router->group(['prefix' => '/{checkpointId}/extensions'], function () use ($router) {
                $router->post('/', ['uses' => 'ExtensionController@create']);
                $router->get('/', ['uses' => 'ExtensionController@indexExtensions']);
            });
        });

        // Choice Schemes
        $router->group(['prefix' => '/choiceschemes'], function () use ($router) {
            $router->get('/{schemeId}', ['uses' => 'ChoiceSchemeController@view']);
            $router->patch('/{schemeId}', ['uses' => 'ChoiceSchemeController@update']);
        });

        // Value Schemes
        $router->group(['prefix' => '/valueschemes'], function () use ($router) {
            $router->get('/{schemeId}', ['uses' => 'ValueSchemeController@view']);
            $router->patch('/{schemeId}', ['uses' => 'ValueSchemeController@update']);

            // Score Conditions
            $router->group(['prefix' => '/{schemeId}/conditions'], function () use ($router) {
                $router->post('/', ['uses' => 'ScoreConditionController@create']);
                $router->get('/', ['uses' => 'ScoreConditionController@index']);
            });
        });

        // Score Conditions
        $router->group(['prefix' => '/conditions'], function () use ($router) {
            $router->get('/{conditionId}', ['uses' => 'ScoreConditionController@view']);
            $router->patch('/{conditionId}', ['uses' => 'ScoreConditionController@update']);
            $router->delete('/{conditionId}', ['uses' => 'ScoreConditionController@delete']);
        });

        // Extensions
        $router->group(['prefix' => '/extensions'], function () use ($router) {
            $router->get('/{extensionId}', ['uses' => 'ExtensionController@view']);
            $router->patch('/{extensionId}', ['uses' => 'ExtensionController@update']);
            $router->delete('/{extensionId}', ['uses' => 'ExtensionController@delete']);
        });

        // Notifications
        $router->group(['prefix' => 'notifications'], function () use ($router) {
            $router->get('/', ['uses' => 'NotificationController@index']);
            $router->patch('/read/{notificationId}', ['uses' => 'NotificationController@markAsRead']);
            $router->post('/', ['uses' => 'NotificationController@create']);
        });

        // Media
        $router->group(['prefix' => 'media'], function () use ($router) {
            $router->get('/', ['uses' => 'MediaController@index']);
            $router->post('/', ['uses' => 'MediaController@create']);
        });

        //hardreset
        $router->group(['prefix' => 'hardreset'], function () use ($router) {
            $router->get('/{companyId}', ['uses' => 'HardResetController@delete']);
        });

        $router->group(['prefix' => 'payment'], function () use ($router) {
            $router->post('/create', ['uses' => 'PaymentController@createSubscription']);
        });

        $router->group(['prefix' => 'dashboard'], function () use ($router) {
            $router->get('/', ['uses' => 'DashboardController@index']);
            $router->get('/{object}', ['uses' => 'DashboardController@reload']);
        });

        $router->group(['prefix' => 'analytics'], function () use ($router) {
            $router->get('/', ['uses' => 'AnalyticsController@index']);
            $router->get('/export', ['uses' => 'AnalyticsController@export']);
        });

        $router->group(['prefix' => 'export'], function () use ($router) {
            $router->get('/audit/pdf/{auditId}', 'AuditController@exportSingle');
        });
    });
});
