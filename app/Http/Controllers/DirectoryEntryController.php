<?php

namespace App\Http\Controllers;

use App\DirectoryEntry;
use App\Directory;
use App\Checklist;
use App\ChecklistEntry;
use App\Checkpoint;
use App\Section;
use App\Extension;
use App\TextfieldExtension;
use App\LocationExtension;
use App\ParticipantExtension;
use App\PictureExtension;
use Ramsey\Uuid\Uuid;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

class DirectoryEntryController extends Controller
{
    /**
     * Moves a checklist or a directory.
     *
     * @param Request $request
     * @param string $checklistId
     * @return \Illuminate\Http\JsonResponse
     */

    public function move(Request $request, string $entryId)
    {
        $data = $request->all();
        $directoryEntry = DirectoryEntry::where('objectType', '=', $data['objectType'])->where('objectId', '=', $entryId)->first();
        $object = NULL;
        if($data['objectType'] == "directory") {
        	$object = Directory::findOrFail($entryId);
        } else if($data['objectType'] == "checklist") {
        	$object = Checklist::findOrFail($entryId);
        } else {
        	return response('', Response::HTTP_UNPROCESSABLE_ENTITY);
        }

       	$this->authorize('update', $object);

        if(is_null($directoryEntry)) {
            $directoryEntry = new DirectoryEntry();
            $directoryEntry->objectId = $entryId;
            $directoryEntry->objectType = $data['objectType'];
        }

        $directoryEntry->parentId = $data['targetId'];
        $directoryEntry->save();

        return response('', Response::HTTP_NO_CONTENT);
    }

    /**
     * Moves a set of checklists or/and directories.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */

    public function moveSet(Request $request)
    {
        $data = $request->all();

        foreach($data['entries'] as $entry) {
            $directoryEntry = DirectoryEntry::where('objectType', '=', $entry['objectType'])->where('objectId', '=', $entry['objectId'])->first();
            $object = NULL;
            if($entry['objectType'] == "directory") {
                $object = Directory::findOrFail($entry['objectId']);
            } else if($entry['objectType'] == "checklist") {
                $object = Checklist::findOrFail($entry['objectId']);
            } else {
                return response('', Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $this->authorize('update', $object);

            if(is_null($directoryEntry)) {
                $directoryEntry = new DirectoryEntry();
                $directoryEntry->objectId = $entry['objectId'];
                $directoryEntry->objectType = $entry['objectType'];
            }

            $directoryEntry->parentId = $entry['targetId'];
            $directoryEntry->save();
        }

        return response('', Response::HTTP_NO_CONTENT);
    }

    /**
     * copies a checklist or a directory.
     *
     * @param Request $request
     * @param string $checklistId
     * @return \Illuminate\Http\JsonResponse
     */

    public function copy(Request $request, string $entryId)
    {
        $data = $request->all();

        $entry = DirectoryEntry::where('objectType', '=', $data['objectType'])->where('objectId', '=', $entryId)->first();
        if(is_null($entry)) {
            return response('', Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if($entry['objectType'] == "directory") {
            $object = Directory::findOrFail($entry['objectId']);
            $this->authorize('update', $object);
            $this->copyDirectory($object, $data['targetId']);
        } else if($entry['objectType'] == "checklist") {
            $object = Checklist::findOrFail($entry['objectId']);
            $this->authorize('update', $object);
            $this->copyChecklist($object, $data['targetId']);
        } else {
            return response('', Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return response('', Response::HTTP_NO_CONTENT);
    }

    public function copySet(Request $request)
    {
        $data = $request->all();

        foreach($data['entries'] as $item)
        {
            $entry = DirectoryEntry::where('objectType', '=', $item['objectType'])->where('objectId', '=', $item['objectId'])->first();
            if(is_null($entry)) {
                return response('', Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            if($entry['objectType'] == "directory") {
                $object = Directory::findOrFail($entry['objectId']);
                $this->authorize('update', $object);
                $this->copyDirectory($object, $item['targetId']);
            } else if($entry['objectType'] == "checklist") {
                $object = Checklist::findOrFail($entry['objectId']);
                $this->authorize('update', $object);
                $this->copyChecklist($object, $item['targetId']);
            } else {
                return response('', Response::HTTP_UNPROCESSABLE_ENTITY);
            }
        }

        return response('', Response::HTTP_NO_CONTENT);
    }

    private function copyDirectory(Directory $directory, string $parentId) {
        $new = new Directory();
        $new->id = Uuid::uuid4()->toString();
        while($directory->id == $new->id) {
            $new->id = Uuid::uuid4()->toString();
        }
        $new->name = $directory->name . ' Copy';
        $new->description = $directory->description;
        $new->icon = $directory->icon;
        $new->company()->associate($directory->company);
        $new->save();

        $entry = new DirectoryEntry();
        $entry->objectType = 'directory';
        $entry->objectId = $new->id;
        $entry->parentId = $parentId;
        $entry->save();

        if(!is_null($directory->entries) && count($directory->entries) > 0)
        {
            foreach($directory->entries as $entry) {
                if($entry['objectType'] == "directory") {
                    $object = Directory::findOrFail($entry['objectId']);
                    $this->copyDirectory($object, $new->id);
                } else if($entry['objectType'] == "checklist") {
                    $object = Checklist::findOrFail($entry['objectId']);
                    $this->copyChecklist($object, $new->id);
                }
            }
        }
        return true;
    }

    private function copyChecklist(Checklist $checklist, string $parentId) {
        $new = new Checklist();
        $new->id = Uuid::uuid4()->toString();
        while($checklist->id == $new->id) {
            $new->id = Uuid::uuid4()->toString();
        }
        $new->name = $checklist->name . ' Copy';
        $new->description = $checklist->description;
        $new->icon = $checklist->icon;
        $new->company()->associate($checklist->company);
        $new->save();

        $entry = new DirectoryEntry();
        $entry->objectType = 'checklist';
        $entry->objectId = $new->id;
        $entry->parentId = $parentId;
        $entry->save();

        if(!is_null($checklist->entries)) {
            foreach($checklist->entries as $entry) {
                $this->copyChecklistEntry($entry, $new->id, 'checklist');
            }
        }

        return true;
    }

    private function copyChecklistEntry(ChecklistEntry $entry, string $parentId, string $parentType) {
        if($entry->objectType == 'section') {
            $object = Section::findOrFail($entry->objectId);
            $this->copySection($object, $parentId, $parentType);
        } else if($entry->objectType == 'extension') {
            $object = Extension::findOrFail($entry->objectId);
            $this->copyExtension($object, $parentId, $parentType);
        } else if($entry->objectType == 'checkpoint') {
            $object = Checkpoint::findOrFail($entry->objectId);
            $this->copyCheckpoint($object, $parentId, $parentType);
        } else {
            return response('', Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        return true;
    }

    private function copySection(Section $section, string $parentId, string $parentType) {
        $new = new Section();
        $new->id = Uuid::uuid4()->toString();
        while($section->id == $new->id) {
            $new->id = Uuid::uuid4()->toString();
        }
        $new->title = $section->title;
        $new->index = $section->index;
        $new->save();

        $newEntry = new ChecklistEntry();
        $newEntry->objectType = 'section';
        $newEntry->objectId = $new->id;
        $newEntry->parentId = $parentId;
        $newEntry->parentType = $parentType;
        $newEntry->save();

        if(!is_null($section->entries)) {
            foreach($section->entries as $entry) {
                $this->copyChecklistEntry($entry, $new->id, 'section');
            }
        }

        return true;
    }

    private function copyCheckpoint(Checkpoint $checkpoint, string $parentId, string $parentType) {
        $new = new Checkpoint();
        $new->id = Uuid::uuid4()->toString();
        while($checkpoint->id == $new->id) {
            $new->id = Uuid::uuid4()->toString();
        }
        $new->prompt = $checkpoint->prompt;
        $new->description = $checkpoint->description;
        $new->mandatory = $checkpoint->mandatory;
        $new->factor = floatval($checkpoint->factor);
        $new->index = $checkpoint->index;
        $new->scoringScheme()->associate($checkpoint->scoringScheme);
        $new->evaluationScheme()->associate($checkpoint->evaluationScheme);
        $new->save();

        $newEntry = new ChecklistEntry();
        $newEntry->objectType = 'checkpoint';
        $newEntry->objectId = $new->id;
        $newEntry->parentId = $parentId;
        $newEntry->parentType = $parentType;
        $newEntry->save();

        if(!is_null($checkpoint->entries)) {
            foreach($checkpoint->entries as $entry) {
                $this->copyChecklistEntry($entry, $new->id, 'checkpoint');
            }
        }

        if(!is_null($checkpoint->extensions)) {
            foreach($checkpoint->extensions as $extension) {
                $this->copyExtensionEntry($extension, $new->id, 'checkpoint');
            }
        }

        return true;
    }

    private function copyExtension(Extension $extension, string $parentId, string $parentType) {
        $object = null;

        if($extension->objectType == 'textfield') {
            $old = TextfieldExtension::findOrFail($extension->objectId);
            $object = new TextfieldExtension();
            $object->text = $old->text;
            $object->fixed = $old->fixed;
        } elseif($extension->objectType == 'location') {
            $old = LocationExtension::findOrFail($extension->objectId);
            $object = new LocationExtension();
            $object->locationId = $old->locationId;
            $object->fixed = $old->fixed;
        } elseif($extension->objectType == 'participant') {
            $old = ParticipantExtension::findOrFail($extension->objectId);
            $object = new ParticipantExtension();
            $object->userId = $old->userId;
            $object->external = $old->external;
            $object->fixed = $old->fixed;
        } elseif($extension->objectType == 'picture') {
            $old = PictureExtension::findOrFail($extension->objectId);
            $object = new PictureExtension();
            $object->image = $old->image;
            $object->type = $old->type;
        } else {
            return response('', Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $object->id = Uuid::uuid4()->toString();
        $object->save();

        $new = new Extension();
        $new->id = Uuid::uuid4()->toString();
        while($extension->id == $new->id) {
            $new->id = Uuid::uuid4()->toString();
        }
        $new->objectType = $extension->objectType;
        $new->objectId = $object->id;
        $new->index = $extension->index;
        $new->save();

        $newEntry = new ChecklistEntry();
        $newEntry->objectType = 'extension';
        $newEntry->objectId = $new->id;
        $newEntry->parentId = $parentId;
        $newEntry->parentType = $parentType;
        $newEntry->save();

        return true;
    }

}
