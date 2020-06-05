<?php

namespace App\Listeners\Checklist;

use App\Events\Checklist\ChecklistEntryUpdatedEvent;
use App\Events\Checklist\ChecklistUpdatedEvent;
use App\ChecklistEntry;
use App\Checklist;
use App\Section;
use App\User;
use Illuminate\Support\Carbon;

class UpdatedChecklistEntryListener
{
    private $models = [
        Checklist::class,
        Section::class,
    ];
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
     * @param  ChecklistEntryUpdatedEvent  $event
     * @return void
     */
    public function handle(ChecklistEntryUpdatedEvent $event)
    {
        $object = ChecklistEntry::where('objectId', '=', $event->objectId)->firstOrFail();
        $checklist = $this->findChecklist($object->id);
        $checklist->lastUpdatedBy = User::findOrFail($event->userId);
        $checklist->updatedAt = $event->time;
        $checklist->save();

        event(new ChecklistUpdatedEvent($checklist->id, $userId, $event->time()));
    }

    private function findChecklist($objectId)
    {
        $object = findIn($objectID, $this->models);

        if(is_a($object, 'Checklist')) {
            return $object;
        }

        return findChecklist($object->parentId);
    }

     private function findIn($id, $models)
    {
        $subject = null;
        foreach ($models as $model) {
            $subject = $model->find($id);
            if (!is_null($subject)) {
                break;
            }
        }
        return $subject;
    }
}
