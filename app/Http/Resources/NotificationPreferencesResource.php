<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

/**
 * @property string $id
 * @property string $userId
 * @property boolean checklistNeedsActivity
 * @property boolean checklistCompleted
 * @property boolean checklistDue
 * @property boolean checlistAssigned
 * @property boolean taskCompleted
 * @property boolean taskAssigned
 * @property \Illuminate\Support\Carbon $createdAt
 * @property \Illuminate\Support\Carbon $updatedAt
 * @property \Illuminate\Support\Carbon $readAt
 */
class NotificationPreferencesResource extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'userId' => $this->userId,
            'checklistNeedsActivityNotification' => $this->checklistNeedsActivityNotification,
            'checklistCompletedNotification' => $this->checklistCompletedNotification,
            'checklistDueNotification' => $this->checklistDueNotification,
            'checklistAssignedNotification' => $this->checklistAssignedNotification,
            'checklistCriticalRatingNotification' => $this->criticalRatingNotification,

            'taskOverdueNotification' => $this->taskOverdueNotification,
            'taskCompletedNotification' => $this->taskCompletedNotification,
            'taskAssignedNotification' => $this->taskAssignedNotification,
            'taskUpdatedNotification' => $this->taskUpdatedNotification,
            'taskDeletedNotification' => $this->taskDeletedNotification,

            'checklistNeedsActivityMail' => $this->checklistNeedsActivityMail,
            'checklistCompletedMail' => $this->checklistCompletedMail,
            'checklistDueMail' => $this->checklistDueMail,
            'checklistAssignedMail' => $this->checklistAssignedMail,
            'checklistCriticalRatingMail' => $this->criticalRatingMail,

            'taskOverdueMail' => $this->taskOverdueMail,
            'taskCompletedMail' => $this->taskCompletedMail,
            'taskAssignedMail' => $this->taskAssignedMail,
            'taskUpdatedMail' => $this->taskUpdatedMail,
            'taskDeletedMail' => $this->taskDeletedMail,

            'auditAssignedNotification' => $this->auditAssignedNotification,
            'auditCompletedNotification' => $this->auditCompletedNotification,
            'auditOverdueNotification' => $this->auditOverdueNotification,
            'auditReleaseRequiredNotification' => $this->auditReleaseRequiredNotification,

            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
        ];
    }
}
