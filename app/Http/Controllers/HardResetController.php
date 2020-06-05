<?php

namespace App\Http\Controllers;

use App\Company;

use App\Address;
use App\AccessGrant;
use App\Checklist;
use App\ChecklistEntry;
use App\Directory;
use App\DirectoryEntry;
use App\Group;
use App\Location;
use App\LocationState;
use App\LocationType;
use App\ReportSettings;
use App\Task;
use App\TaskPriority;
use App\TaskState;
use App\TaskType;
use App\ScoreScheme;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

class HardResetController extends Controller
{
	public function delete(Request $request, string $companyId)
	{
		$company = Company::findOrFail($companyId);

		$user = $request->user();

		if (!$user->isSuperAdmin() && (!$user->isAdmin() || !$user->company->is($company)))
		{
            return Response('', Response::HTTP_FORBIDDEN);
        }

		$this->deleteAddresses($company->id);
		$this->deleteChecklist($company->id);
		$this->deleteDirectories($company->id);
		$this->deleteGroups($company->id);
		$this->deleteLocations($company->id);
		$this->deleteLocationStates($company->id);
		$this->deleteLocationTypes($company->id);
		$this->deleteReportSettings($company->id);
		$this->deleteTasks($company->id);
		$this->deleteTaskPriorities($company->id);
		$this->deleteTaskStates($company->id);
		$this->deleteTaskTypes($company->id);

		return Response('', Response::HTTP_NO_CONTENT);
	}

	private function deleteAddresses($companyId)
	{
		Address::where('companyId', '=', $companyId)->where('typeId', '!=', 'billing')->delete();
	}

	private function deleteChecklist($companyId)
	{
		$checklists = Checklist::where('companyId', '=', $companyId)->get();

		foreach($checklists as $checklist)
		{
			$this->detachAndDelete($checklist->scoringSchemes(), ScoreScheme::class);
			$this->detachAndDelete($checklist->entries(), ChecklistEntry::class);
			$this->detachAndDelete($checklist->grants(), AccessGrant::class);

			$checklist->delete();
		}
	}

	private function deleteDirectories($companyId)
	{
		$directories = Directory::where('companyId', '=', $companyId)->get();

		foreach($directories as $directory)
		{
			$this->detachAndDelete($directory->entries(), DirectoryEntry::class);
			$this->detachAndDelete($directory->grants(), AccessGrant::class);

			$directory->delete();
		}
	}

	private function deleteGroups($companyId)
	{
		$groups = Group::where('companyId', '=', $companyId)->get();

		foreach($groups as $group)
		{
			$this->detachAndDelete($group->grants(), AccessGrant::class);

			$group->delete();
		}
	}

	private function deleteLocations($companyId)
	{
		Location::where('companyId', '=', $companyId)->delete();
	}

	private function deleteLocationStates($companyId)
	{
		LocationState::where('companyId', '=', $companyId)->delete();
	}

	private function deleteLocationTypes($companyId)
	{
		LocationType::where('companyId', '=', $companyId)->delete();
	}

	private function deleteReportSettings($companyId)
	{
		ReportSettings::where('companyId', '=', $companyId)->delete();
	}

	private function deleteTasks($companyId)
	{
		Task::where('companyId', '=', $companyId)->delete();
	}

	private function deleteTaskPriorities($companyId)
	{
		TaskPriority::where('companyId', '=', $companyId)->delete();
	}

	private function deleteTaskStates($companyId)
	{
		TaskState::where('companyId', '=', $companyId)->delete();
	}

	private function deleteTaskTypes($companyId)
	{
		TaskType::where('companyId', '=', $companyId)->delete();
	}

	private function detachAndDelete($collection, $model)
	{
		$ids = [];

		foreach($collection as $item)
		{
			$ids[] = $item->id;
			$item->detach();
		}

		$model::whereIn('id', $ids)->delete();
	}
}
