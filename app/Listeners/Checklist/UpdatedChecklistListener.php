<?php

namespace App\Listeners\Checklist;

use App\Events\Checklist\ChecklistUpdatedEvent;
use App\DirectoryEntry;
use App\Directory;
use Illuminate\Support\Carbon;

class UpdatedChecklistListener
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
     * @param  ChecklistUpdatedEvent  $event
     * @return void
     */
    public function handle(ChecklistUpdatedEvent $event)
    {
        $directoryEntry = DirectoryEntry::findOrFail($event->objectId);
        $this->updateDirectories($directoryEntry->parentId, $event->userId, $event->time);

    }

    private function updateDirectories(string $objectId, string $userId, $time)  
    {
        $directoryEntry = DirectoryEntry::findOrFail($event->objectId);
        $directory = Directory::findOrFail($directoryEntry->objectId);

        $directory->lastUpdatedBy = $userId;
        $directory->updatedAt = $time;

        if(!is_null($directoryEntry->parentId)) {
            $this->updateDirectories($directoryEntry->parentId, $userId, $time);
        }
    }
}
