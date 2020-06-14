<?php

namespace App\Http\Controllers;

use App\Audit;
use App\User;
use App\Checklist;
use App\Check;
use App\Checkpoint;
use App\ValueCheck;
use App\ChoiceCheck;
use App\Company;
use App\AuditState;
use App\Score;
use App\ScoreNotification;
use App\Extension;
use App\Directory;
use App\DirectoryEntry;
use App\LocationCheck;
use App\PlannedAudit;
use App\LocationExtension;
use App\Events\Checklist\ChecklistEscalationEvent;
use App\Events\Checklist\ChecklistCriticalRatingEvent;
use App\Events\Audit\AuditFinishedEvent;
use App\Http\Resources\AuditResource;
use App\Http\Resources\AuditExportResource;
use App\Http\Resources\CheckResource;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Carbon\Carbon;
use GuzzleHttp\Client;

class AuditController extends Controller {
    /**
     * @var Audit
     */

    private $audit;
    private $audits;

    /**
     * Create a new controller instance.
     *
     * @param Audit $audit
     */

    public function __contsruct(Audit $audit) {
        $this->audit = $audit;
    }

    /**
     * Create a new audit.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */

    public function create(Request $request) {
        $data = $this->validate($request, Audit::rules('create'));

        $audit = new Audit($data);

        $audit->company()->associate(Company::findOrFail($data['company']));
        $audit->user()->associate(User::findOrFail($data['user']));
        $audit->checklist()->associate(Checklist::findOrFail($data['checklist']));
        $audit->state()->associate(AuditState::findOrFail($data['state']));

        $this->authorize($audit);
        $audit->save();

        return AuditResource::make($audit)->response()->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * Update an audit.
     *
     * @param Request $request
     * @param string $auditId
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */

    public function update(Request $request, string $auditId) {
        $data = $this->validate($request, Audit::rules('update'));

        $audit = Audit::findOrFail($auditId);
        $this->authorize($audit);

        if (array_key_exists('executionDue', $data)) {
            $audit->executionDue = $data['executionDue'];
        }

        if (array_key_exists('company', $data)) {
            $audit->company()->associate(Company::findOrFail($data['company']));
        }

        if (array_key_exists('user', $data)) {
            $audit->user()->associate(User::findOrFail($data['user']));
        }


        if (array_key_exists('checklist', $data)) {
            $audit->checklist()->associate(Checklist::findOrFail($data['checklist']));
        }

        if (array_key_exists('state', $data)) {
            $state = AuditState::findOrFail($data['state']);

            if ($state->name == 'finished') {
                $checklist = $audit->checklist;

                if ($checklist->needsApproval == 1) {
                    $state = AuditState::where('name', '=', 'awaiting approval')->first();

                    foreach ($checklist->approvingUsers as $user) {
                        event(new ChecklistEscalationEvent($checklist, $user, $audit));
                    }

                    foreach ($checklist->approvingUserGroups as $group) {
                        foreach ($group->users as $user) {
                            event(new ChecklistEscalationEvent($checklist, $user, $audit));
                        }
                    }
                }

                $time = Carbon::now();

                $checklist = Checklist::findOrFail($data['checklist']);
                $checklist->lastUsedBy = $data['user'];
                $checklist->usedAt = $time;
                $checklist->timestamps = false;
                $checklist->save();

                $this->handlePlannedAudit($checklist, Carbon::now());

                $directoryEntry = DirectoryEntry::where('objectId', '=', $checklist->id)->where('objectType', '=', 'checklist')->first();
                $this->updateDirectories($directoryEntry->parentId, $data['user'], $time);
            }

            $audit->state()->associate($state);

            if ($state->name == 'finished' || $state->name == 'approved') {
                $checklist = Checklist::find($audit->checklistId);

                $checks = Check::where('auditId', '=', $audit->id)->where('objectType', '=', 'checkpoint')->get();

                $users = [];

                foreach ($checks as $check) {
                    $object = null;
                    if ($check->valueType == 'choice') {
                        $object = ChoiceCheck::where('checkId', '=', $check->id)->first();

                    } else if ($check->valueType == 'value') {
                        $object = ValueCheck::where('checkId', '=', $check->id)->first();
                    }

                    

                    $question = Checkpoint::findOrFail($check->checkpointId);

                    if(isset($object->scoreId)) {
                        $notifications = ScoreNotification::where('scoreId', '=', $object->scoreId)->get();

                        foreach($notifications as $notification) {
                            if($notification->objectType == 'user') {
                                if (!in_array($notification->objectType, $users)) {
                                    $users[$notification->objectId] = [];
                                }
                                $users[$notification->objectId][] = [
                                    'prompt' => $question->prompt,
                                    'value' => $object->value,
                                ];
                            }
                        }
                    }
                }

                if (count($users) > 0) {
                    foreach ($users as $id => $criticals) {
                        $user = User::findOrFail($id);

                        event(new ChecklistCriticalRatingEvent($checklist, $user, $audit, $criticals));
                    }
                }
            }
        }

        $audit->save();

        return response($users, Response::HTTP_OK);
    }

    private function handlePlannedAudit($checklist, $date) {
        $audits = PlannedAudit::where('checklistId', '=', $checklist->id)->where('date', '=', $date->format('Y-m-d'))->where('startTime', '<=', $date->format('H:i:s'))->where('endTime', ">=", $date->format('H:i:s'))->where('wasExecuted', '=', 0)->first();
        if(!is_null($audits)) {
            $audits->wasExecuted = 1;
            $audits->save();
        }
    }

    private function updateDirectories(string $objectId, string $userId, $time)
    {
        $directoryEntry = DirectoryEntry::where('objectId', '=', $objectId)->first();
        $directory = Directory::findOrFail($objectId);

        $directory->lastUsedBy = $userId;
        $directory->usedAt = $time;
        $directory->timestamps = false;
        $directory->save();

        if(!is_null($directoryEntry) && !is_null($directoryEntry->parentId)) {
            $this->updateDirectories($directoryEntry->parentId, $userId, $time);
        }
    }

    /**
     * Deletes an audit.
     *
     * @param Request $request
     * @param string $auditId
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */

    public function delete(Request $request, string $auditId) {
        $audit = Audit::findOrFail($auditId);
        $this->authorize($audit);

        $audit->delete();

        return response('', Response::HTTP_NO_CONTENT);
    }

    /**
     * archives an audit.
     *
     * @param Request $request
     * @param string $auditId
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */

    public function archive(Request $request, string $auditId) {
        $audit = Audit::findOrFail($auditId);
        $this->authorize('update', $audit);

        $audit->isArchived = 1;
        $audit->save();

        return response('', Response::HTTP_NO_CONTENT);
    }

    /**
     * archives a set of audits.
     *
     * @param Request $request
     * @param string $auditId
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */

    public function archiveSet(Request $request) {
        $data = $request->all();
        foreach ($data['items'] as $item) {
            $audit = Audit::findOrFail($item);
            $this->authorize('update', $audit);

            $audit->isArchived = 1;
            $audit->save();
        }

        return response('', Response::HTTP_NO_CONTENT);
    }

    /**
     * restores an archived audit.
     *
     * @param Request $request
     * @param string $auditId
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */

    public function restore(Request $request, string $auditId) {
        $audit = Audit::findOrFail($auditId);
        $this->authorize('update', $audit);

        $audit->isArchived = 0;
        $audit->save();

        return response('', Response::HTTP_NO_CONTENT);
    }

    /**
     * restores a set of audits.
     *
     * @param Request $request
     * @param string $auditId
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */

    public function restoreSet(Request $request) {
        $data = $request->all();
        foreach ($data['items'] as $item) {
            $audit = Audit::findOrFail($item);
            $this->authorize('update', $audit);

            $audit->isArchived = 0;
            $audit->save();
        }

        return response('', Response::HTTP_NO_CONTENT);
    }

    /**
     * List a companies audits.
     *
     * @param Request $request
     * @param string|null $companyId
     * @return \Illuminate\Http\JsonResponse
     */

    public function index(Request $request, string $companyId) {
        $user = $request->user();

        $company = Company::findOrFail($companyId);

        $audits = Audit::where('companyId', '=', $company->id);
        $audits->where('isArchived', '=', 0);

        if (!$user->isAdmin() && !$user->isSuperAdmin()) {
            $audits->where('userId', '=', $user->id);
        }

        $audits = $this->filterRequest($request->all(), $audits, $user);

        return AuditResource::collection($audits->get())->response()->setStatusCode(Response::HTTP_OK);
    }

    /**
     * List a companies archived audits.
     *
     * @param Request $request
     * @param string|null $companyId
     * @return \Illuminate\Http\JsonResponse
     */

    public function indexArchive(Request $request, string $companyId) {
        $user = $request->user();

        $company = Company::findOrFail($companyId);

        $audits = Audit::where('companyId', '=', $company->id);
        $audits->where('isArchived', '=', 1);

        if (!$user->isAdmin() && !$user->isSuperAdmin()) {
            $audits->where('userId', '=', $user->id);
        }

        $audits = $this->filterRequest($request->all(), $audits, $user);

        return AuditResource::collection($audits->get())->response()->setStatusCode(Response::HTTP_OK);
    }

    public function getExecutedAuditsResults(Request $request, string $entryId) {
        $entry = DirectoryEntry::findOrFail($entryId);

        if ($entry->objectType == "directory") {
            $this->getAuditsInDirectory($entry);
        } else if ($entry->objectType == "checklist") {
            $state = AuditState::where('name', '=', 'finished')->first()->id;
            $audits = Audit::where('checklistId', '=', $entry->objectId)->where('statId', '=', $state);

            $audits = $this->filterRequest($request->all(), $audits, $user)->first();
        } else {
            return response('', Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $results = [];

        foreach ($this->audits as $audit) {
            foreach ($audit->results as $check) $results[] = $check;
        }

        return CheckResource::collection(array_unique($results))->response()->setStatusCode(Response::HTTP_OK);
    }

    public function getExecutedAudits(Request $request, string $entryId) {
        $entry = DirectoryEntry::where('objectId', '=', $entryId)->firstOrFail();
        $audits = null;

        if ($entry->objectType == "directory") {
            $audits = $this->getAuditsInDirectory($entry, $request);

        } else if ($entry->objectType == "checklist") {
            $state = AuditState::where('name', '=', 'finished')->first()->id;
            $audits = Audit::where('checklistId', '=', $entry->objectId)->where('stateId', '=', $state)->get();

            $audits = $this->filterRequest($request->all(), $audits, $request->user());

        } else {
            return response('', Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ($audits !== null && count($audits) > 0) {
            return AuditResource::collection($audits)->response()->setStatusCode(Response::HTTP_OK);
        } else {
            return response('{"data":[]}', Response::HTTP_OK);
        }
    }

    private function getAuditsInDirectory(DirectoryEntry $entry, Request $request = null) {
        $children = DirectoryEntry::where('parentId', '=', $entry->objectId)->get();
        $audits = collect();

        foreach($children as $child) {
            if($child->objectType == 'directory') {
                $audits = $audits->concat($this->getAuditsInDirectory($child, $request));
            } else if($child->objectType == 'checklist') {
                $state = AuditState::where('name', '=', 'finished')->first()->id;
                $audits = $audits->concat(Audit::where('checklistId', '=', $child->objectId)->where('stateId', '=', $state)->get());
            }
        }

        return $audits;
    }

    /**
     * View a audit.
     *
     * @param string $auditId
     * @return \Illuminate\Http\JsonResponse
     */

    public function view(Request $request, string $auditId) {
        $audit = Audit::findOrFail($auditId);

        $this->authorize($audit);

        return AuditResource::make($audit)->response()->setStatusCode(Response::HTTP_OK);
    }

    public function exportSingle(Request $request, string $auditId) {
        $audit = Audit::findOrFail($auditId);

        // ToDo: set PDF GENERATOR RESOURCE for single export

        $key = env('PDF_GENERATOR_KEY');
        $workspace = env('PDF_GENERATOR_WORKSPACE');
        $secret = env('PDF_GENERATOR_SECRET');
        $resource = '';
        $documentData = json_encode(new AuditExportResource($audit));

        $data = [
            'key' => $key,
            'resource' => $resource,
            'workspace' => $workspace
        ];
        ksort($data);

        $signature = hash_hmac('sha256', implode('', $data), $secret);

        $client = new Client([
            'base_uri' => 'https://us1.pdfgeneratorapi.com/api/v3/',
        ]);

        /**
         * Authentication params sent in headers
         */
        $response = $client->request('POST', $resource,  [
            'body' => $documentData,
            'query' => [
                'format' => 'pdf',
                'output' => 'url'
            ],
            'headers' => [
                'X-Auth-Key' => $key,
                'X-Auth-Workspace' => $workspace,
                'X-Auth-Signature' => $signature,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json; charset=utf-8',
            ]
        ]);

        $contents = $response->getBody()->getContents();

        return Response($contents,  Response::HTTP_OK);
    }

    /**
     * filters audits
     *
     * @param $request
     * @param $tasks
     * @param $user
     * @return  $audits
     */
    private function filterRequest($request, $audits, $user) {
        if (array_key_exists('state', $request)) {
            $audits = $this->filterByState($request['state'], $audits);
        }

        if (array_key_exists('checklist', $request)) {
            $audits = $this->filterByChecklist($request['checklist'], $audits);
        }

        if (array_key_exists('user', $request)) {
            $audits = $this->filterByUser($request['user'], $audits);
        }

        if (array_key_exists('start', $request) && array_key_exists('end', $request)) {
            $audits = $this->filterByTimePeriod($request['start'], $request['end'], $audits);
        }

        if(array_key_exists('location', $request)) {
            $audits = $this->filterByLocation($request['location'], $audits);
        }

        return $audits;
    }

    private function filterByState($state, $audits) {
        return $audits->where('stateId', '=', $state);
    }

    private function filterByChecklist($checklist, $audits) {
        return $audits->where('checklistId', '=', $checklist);
    }

    private function filterByUser($user, $audits) {
        return $audits->where('userId', '=', $user);
    }

    private function filterByTimePeriod($start, $end, $audits)
    {
        return $audits->where(function($query) use ($start, $end) {
            $query->whereBetween('executionDue', [$start, $end]);
            $query->orWhereBetween('createdAt', [$start, $end]);
        });
    }

    private function filterByLocation($locationId, $audits)
    {
        $auditIds = [];

        $locationExtensions = LocationExtension::where('locationId', '=', $locationId)->get();

        if(count($locationExtensions) > 0) {
            foreach($locationExtensions as $location) {
                //add where conditions for types
                $extension = Extension::where('objectId', '=', $location->id)->first();
                $check = Check::where('objectId', '=', $extension->id)->first();
                if(!is_null($check) && !in_array($check->auditId, $auditIds)) {
                    $auditIds[] = $check->auditId;
                }
            }
        }

        $locationChecks = LocationCheck::where('locationId', '=', $locationId)->get();
        if(count($locationChecks) > 0) {
            foreach($locationChecks as $location) {
                $check = Check::findOrFail($location->checkId);
                if(!in_array($check->auditId, $auditIds)) {
                    
                    $auditIds[] = $check->auditId;
                }
            }

        }
        return $audits->whereIn('id', $auditIds);
    }
}
