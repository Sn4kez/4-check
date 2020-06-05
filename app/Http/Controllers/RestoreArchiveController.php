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

class RestoreArchiveController extends Controller
{
    public function restore(Request $request, string $entryId)
    {
    	$entry = ArchiveEntry::findOrFail($entryId);

    	$company = Company::findOrFail($request->input('company'));

        $user = $request->user();

        if(!$user->isSuperAdmin() && (!$user->isAdmin() || !$user->company->is($company)))
        {
            return Response('', Response::HTTP_FORBIDDEN);
        }    

        $this->restoreEntry($entry);
        $entry->delete();

        return response('', Response::HTTP_NO_CONTENT);
    }

    public function restoreSet(Request $request)
    {
    	$company = Company::findOrFail($request->input('company'));

    	$user = $request->user();

    	$items = $request->input('items');

    	foreach($items as $entryId) {
	    	$entry = ArchiveEntry::findOrFail($entryId);

	        if(!$user->isSuperAdmin() && (!$user->isAdmin() || !$user->company->is($company)))
	        {
	            return Response('', Response::HTTP_FORBIDDEN);
	        }      

	        $this->restoreEntry($entry);
            $entry->delete();
    	}

        return response('', Response::HTTP_NO_CONTENT);
    }

    private function restoreEntry(ArchiveEntry $entry) {
    	if($entry->objectType == 'archive') {
            $dir = ArchiveDirectory::findOrFail($entry->objectId);
    		if(!is_null($dir->entries))
        	{
        		foreach($dir->entries as $directory) {
        			$this->restoreEntry($directory);
        		}
        	}

            $tmp = ArchiveDirectory::find($entry->objectId);

            if(!is_null($tmp)) {
                $tmp->delete();
            }
    	}

    	if(!is_null($entry->referenceId)) {
			$dirEntry = DirectoryEntry::findOrFail($entry->referenceId);

	        $dirEntry->archived = 0;
	        $dirEntry->save();
        }

        $entry->delete();

        return true;
    }
}
