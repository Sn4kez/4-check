<?php

namespace App\Listeners\Audit;

use App\Events\Audit\AuditFinishedEvent;
use App\Checklist;
use App\DirectoryEntry;
use App\Directory;

class FinishedAuditListener
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
     * @param  AuditFinishedEvent  $event
     * @return void
     */
    public function handle(AuditFinishedEvent $event)
    {
        $checklist = Checklist::findOrFail($event->objectId);
        $checklist->lastUsedBy = $event->userId;
        $checklist->usedAt = $event->time;
        $checklist->save();
        $directoryEntry = DirectoryEntry::findOrFail($checklist->id);
        $this->updateDirectories($directoryEntry->parentId, $event->userId, $event->time);
    }

    private function updateDirectories(string $objectId, string $userId, $time)  
    {
        $directoryEntry = DirectoryEntry::findOrFail($event->objectId);
        $directory = Directory::findOrFail($directoryEntry->objectId);

        $directory->lastUsedBy = $userId;
        $directory->usedAt = $time;

        if(!is_null($directoryEntry->parentId)) {
            $this->updateDirectories($directoryEntry->parentId, $userId, $time);
        }
    }
}
