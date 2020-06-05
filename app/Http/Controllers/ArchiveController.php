<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\DirectoryEntry;
use App\Directory;
use App\Checklist;
use App\ArchiveEntry;
use App\ArchiveDirectory;
use App\Company;
use Ramsey\Uuid\Uuid;

class ArchiveController extends Controller
{
    public function archive(Request $request, string $entryId)
    {
    	$entry = DirectoryEntry::findOrFail($entryId);

        $user = $request->user();

        if($user->isAdmin()) {
            $parent = $user->company->archive->id;
        }

        if($user->isSuperAdmin()) {
            $company = Company::findOrFail($request->input('company'));
            $parent = $company->archive->id;
        }

    	if($entry->objectType == "directory") {
        	$object = Directory::findOrFail($entry->objectId);
        } else if($entry->objectType == "checklist") {
        	$object = Checklist::findOrFail($entry->objectId);
        } else {
        	return response('', Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $this->authorize('update', $object);

        if($entry->objectType == "directory") {
            $this->archiveDirectory($object, $parent);
        } else if($entry->objectType == "checklist") {
            $this->archiveChecklist($object, $parent);
        }

        $entry->archived = 1;
        $entry->save();

        return response('', Response::HTTP_NO_CONTENT);
    }

    public function archiveSet(Request $request)
    {
        $items = $request->input('items');

        $user = $request->user();

        if($user->isAdmin()) {
            $parent = $user->company->archive->id;
        }

        if($user->isSuperAdmin()) {
            $company = Company::findOrFail($request->input('company'));
            $parent = $company->archive->id;
        }

        foreach($items as $entryId) {
            $entry = DirectoryEntry::findOrFail($entryId);

            if($entry->objectType == "directory") {
                $object = Directory::findOrFail($entry->objectId);
            } else if($entry->objectType == "checklist") {
                $object = Checklist::findOrFail($entry->objectId);
            } else {
                return response('', Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $this->authorize('update', $object);

            if($entry->objectType == "directory") {
                $this->archiveDirectory($object, $parent);
            } else if($entry->objectType == "checklist") {
                $this->archiveChecklist($object, $parent);
            }

            $entry->archived = 1;
            $entry->save();
        }

        return response('', Response::HTTP_NO_CONTENT);
    }

    private function archiveDirectory(Directory $directory, string $parentId) {
        
        $new = new ArchiveDirectory();
        $new->id = Uuid::uuid4()->toString();
        $new->name = $directory->name;
        $new->description = $directory->description;
        $new->icon = $directory->icon;
        $new->company()->associate($directory->company);
        $new->save();

        $tmp = DirectoryEntry::where('objectType', '=', 'directory')->where('objectId', '=', $directory->id)->firstOrFail();
        $tmp->archived = 1;
        $tmp->save();

        $entry = new ArchiveEntry();
        $entry->objectType = 'archive';
        $entry->objectId = $new->id;
        $entry->parentId = $parentId;
        $entry->referenceId = $tmp->id;
        $entry->save();

        if(!is_null($directory->entries) && count($directory->entries) > 0)
        {
            foreach($directory->entries as $entry) {
                if($entry['objectType'] == "directory") {
                    $object = Directory::findOrFail($entry['objectId']);
                    $this->archiveDirectory($object, $new->id);
                } else if($entry['objectType'] == "checklist") {
                    $object = Checklist::findOrFail($entry['objectId']);
                    $this->archiveChecklist($object, $new->id);
                }
            }
        }

        return true; 
    }

    private function archiveChecklist(Checklist $checklist, string $parentId) {

        $tmp = DirectoryEntry::where('objectType', '=', 'checklist')->where('objectId', '=', $checklist->id)->first();
        $tmp->archived = 1;
        $tmp->save();

        $entry = new ArchiveEntry();
        $entry->objectType = 'checklist';
        $entry->objectId = $checklist->id;
        $entry->parentId = $parentId;
        $entry->referenceId = $tmp->id;
        $entry->save();

        return true; 
    }
}
