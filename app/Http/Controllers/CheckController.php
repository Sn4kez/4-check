<?php

namespace App\Http\Controllers;

use App\Audit;
use App\Media;
use App\ScoreNotification;
use App\User;
use App\Checklist;
use App\Company;
use App\Check;
use App\Checkpoint;
use App\Extension;
use App\Section;
use App\ChecklistEntry;
use App\ParticipantCheck;
use App\ValueCheck;
use App\ChoiceCheck;
use App\TextfieldCheck;
use App\LocationCheck;
use App\PictureCheck;
use App\ValueScheme;
use App\Http\Resources\CheckResource;
use App\Http\Resources\AuditEntryResource;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Ramsey\Uuid\Uuid;
use App\Notification;

class CheckController extends Controller {
    /**
     * @var Check
     */

    private $check;

    /**
     * @var Audit
     */

    private $audit;

    /**
     * @var Checklist
     */

    private $checklist;

    private $checkpoints = [];
    private $selectedCheckpoints = [];


    /**
     * Create a new controller instance.
     *
     * @param Check $check
     */

    public function __contsruct(Check $check, Audit $audit, Checklist $checklist) {
        $this->check = $check;
        $this->audit = $audit;
        $this->checklist = $checklist;
    }

    /**
     * Create a new check.
     *
     * @param Request $request
     * @param string $auditId
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */

    public function create(Request $request, string $auditId) {
        $audit = Audit::findOrFail($auditId);
        $data = $this->validate($request, Checklist::rules('create'));
        $check = new Check($data);
        $check->auditId = $auditId;

        $this->authorize('update', $audit);

        $check->save();

        return CheckRessource::make($check)->response()->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * Update an check.
     *
     * @param Request $request
     * @param string $checkId
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */

    public function update(Request $request, string $checkId) {
        $data = $this->validate($request, Check::rules('update'));
        $check = Check::findOrFail($checkId);
        $audit = Audit::findOrFail($check->auditId);

        $checkValue = null;
        $checkType = null;


        if ($request->exists('value')) {
            if ($check->objectType == 'checkpoint') {
                $checkpoint = Checkpoint::findOrFail($check->objectId);

                if ($checkpoint->evaluationScheme instanceof ValueScheme) {
                    $checkValue = ValueCheck::firstOrCreate(['checkId' => $checkId]);
                    $checkValue->value = $request->input('value');
                } else {
                    $checkValue = ChoiceCheck::firstOrCreate(['checkId' => $checkId]);
                }

                if ($request->exists('scoreId')) {
                    $scoreId = $request->input('scoreId');
                    $checkValue->scoreId = $scoreId;

                    $notifications = ScoreNotification::where('scoreId', '=', $scoreId);

                    /** @var User $currentUser */
                    $currentUser = $request->user();
                    $currentUserId = $currentUser->id;

                    $notifications->each(function ($item) use ($currentUserId) {
                        $objectType = $item->objectType;

                        if ($objectType === 'user') {
                            $userId = $item->objectId;
                            Notification::addNotification($currentUserId, $userId, '', Notification::TITLE_CHECK_NOTIFICATION, Notification::MESSAGE_CHECK_NOTIFICATION, Notification::PERMISSION_NAME_CHECK_NOTIFICATION);
                        }
                    });
                }

                $checkValue->save();
                $check->rating = $check->factor * $request->input('value');
                $check->save();

                if ($check->object->evaluationSchemeType == 'App\ChoiceScheme') {
                    $checkType = Check::VALUE_TYPE_CHOICE;
                } else {
                    $checkType = Check::VALUE_TYPE_VALUE;
                }

            } elseif ($check->objectType == 'participant') {
                $checkValue = ParticipantCheck::firstOrCreate(['checkId' => $checkId]);
                $checkType = Check::VALUE_TYPE_PARTICIPANTS;
                if ($request->exists('userParticipant')) {
                    $checkValue->participantId = $request->input('userParticipant');
                }
                if ($request->exists('externalParticipant')) {
                    $checkValue->externalParticipant = $request->input('externalParticipant');
                }
                $checkValue->save();
            } elseif ($check->objectType == 'textfield') {
                $checkValue = TextfieldCheck::firstOrCreate(['checkId' => $checkId]);
                $checkType = Check::VALUE_TYPE_TEXTFIELD;
                $checkValue->value = $request->input('value');
                $checkValue->save();
            } elseif ($check->objectType == 'picture') {
                $checkValue = PictureCheck::firstOrCreate(['checkId' => $checkId]);
                $checkType = Check::VALUE_TYPE_PICTURE;

                if ($request->has('value')) {
                    if (!is_null($request->input('value'))) {
                        $data = Media::processRequest(['source_b64' => $request->input('value')]);

                        if (array_key_exists('image', $data)) {
                            $checkValue->value = $data['image'];
                        } else {
                            $checkValue->value = null;
                        }
                    } else {
                        $checkValue->value = null;
                    }
                }

                $checkValue->save();
            } elseif ($check->objectType == 'location') {
                $checkValue = LocationCheck::firstOrCreate(['checkId' => $checkId]);
                $checkValue->locationId = $request->input('value');
                $checkType = Check::VALUE_TYPE_LOCATION;
                $checkValue->save();
            }

            $check->value()->save($checkValue);
        }

        if (!is_null($checkValue)) {
            $check->valueId = $checkValue->id;
            $check->valueType = $checkType;
            $check->save();
        }


        $this->authorize('update', $audit);
        $check->update($data);

        return Response('', Response::HTTP_NO_CONTENT);
    }

    /**
     * Deletes an audit.
     *
     * @param Request $request
     * @param string $checkId
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */

    public function delete(Request $request, string $checkId) {
        $check = Check::findOrFail($checkId);

        $audit = Audit::findOrFail($check->auditId);

        $this->authorize('update', $audit);

        $check->delete();

        return Response('', Response::HTTP_NO_CONTENT);
    }

    /**
     * List a audits checks.
     *
     * @param Request $request
     * @param string|null $auditId
     * @return \Illuminate\Http\JsonResponse
     */

    public function index(Request $request, string $auditId) {
        $checks = Check::where('auditId', '=', $auditId)->get();

        return CheckResource::collection($checks)->response()->setStatusCode(Response::HTTP_OK);
    }

    /**
     * View a check.
     *
     * @param Request $request
     * @param string $auditId
     * @return \Illuminate\Http\JsonResponse
     */

    public function view(Request $request, string $checkId) {
        $check = Check::findOrFail($checkId);
        $audit = Audit::findOrFail($check->auditId);

        $this->authorize('view', $audit);

        return CheckResource::make($check)->response()->setStatusCode(Response::HTTP_OK);
    }

    /**
     * lists entries of audit.
     *
     * @param Request $request
     * @param string $auditId
     * @param string $sectionId
     * @return \Illuminate\Http\JsonResponse
     */

    public function entries(Request $request, string $auditId) {
        $this->audit = Audit::findOrFail($auditId);
        $this->checklist = Checklist::findOrFail($this->audit->checklistId);

        $this->authorize('update', $this->audit);

        $data = $request->all();

        if (array_key_exists('sectionId', $data)) {
            $sectionId = $data['sectionId'];
        } else {
            $sectionId = null;
        }

        if (is_null($sectionId)) {
            $checks = Check::where('auditId', '=', $this->audit->id)->whereNull('parentId')->get();
            $sections = ChecklistEntry::where('objectType', '=', 'section')->where('parentId', '=', $this->checklist->id)->get();
        } else {
            $checks = Check::where('auditId', '=', $this->audit->id)->where('parentId', '=', $sectionId)->get();
            $sections = ChecklistEntry::where('objectType', '=', 'section')->where('parentId', '=', $sectionId)->get();
        }

        $sections->map(function ($section) {
            $section['auditId'] = $this->audit->id;
            return $section;
        });

        $result = [];
        $result['checks'] = CheckResource::collection($checks);
        $result['sections'] = AuditEntryResource::collection($sections, $this->audit->id);

        return Response($result, Response::HTTP_OK);
    }

    /**
     * starts an audit.
     *
     * @param Request $request
     * @param string $auditId
     * @return \Illuminate\Http\JsonResponse
     */

    public function startAudit(Request $request, string $auditId) {
        $this->audit = Audit::findOrFail($auditId);

        $this->authorize('update', $this->audit);

        $checks = Check::where('auditId', '=', $this->audit->id)->whereNull('sectionId')->get();

        if ($checks->count() == 0) {
            $this->checklist = Checklist::findOrFail($this->audit->checklistId);
            if (!is_null($this->checklist->entries)) {
                foreach ($this->checklist->entries as $entry) {
                    $this->getChecklistsCheckpoints($entry);
                }

                if ($this->checklist->chooseRandom && $this->checklist->numberQuestions > 0) {
                    $tmp = array_rand($this->checkpoints, $this->checklist->numberQuestions);

                    if ($this->checklist->numberQuestions > 1) {
                        foreach ($tmp as $item) {
                            $this->selectedCheckpoints[] = $this->checkpoints[$item];
                        }
                    } else {
                        $this->selectedCheckpoints[] = $this->checkpoints[$tmp];
                    }

                } else {
                    $this->selectedCheckpoints = $this->checkpoints;
                }

                foreach ($this->checklist->entries as $entry) {
                    $object = $this->handleChecklistEntry($entry);
                }
            }

            $checks = Check::where('auditId', '=', $this->audit->id)->whereNull('parentId')->get();
        }

        $result = [];
        $result['checks'] = CheckResource::collection($checks);

        $sections = ChecklistEntry::where('objectType', '=', 'section')->where('parentId', '=', $this->checklist->id)->get();

        $result['sections'] = AuditEntryResource::collection($sections);

        return Response($result, Response::HTTP_OK);
    }

    private function getChecklistsCheckpoints($entry, $section = null, $objectType = null) {
        if ($entry->objectType == 'section') {
            $object = Section::findOrFail($entry->objectId);

            if (!is_null($object->entries)) {
                foreach ($object->entries as $entry) {
                    $this->getChecklistsCheckpoints($entry, $object->id, 'section');
                }
            }
        } else if ($entry->objectType == 'checkpoint') {
            $this->checkpoints[] = $entry->objectId;
        }
    }

    private function handleChecklistEntry($entry, $section = null, $objectType = null) {
        if ($entry->objectType == 'section') {
            $object = Section::findOrFail($entry->objectId);

            if (!is_null($object->entries)) {
                foreach ($object->entries as $entry) {
                    $this->handleChecklistEntry($entry, $object->id, 'section');
                }
            }

        } else if ($entry->objectType == 'extension') {
            $object = Extension::findOrFail($entry->objectId);

            $check = new Check();
            $check->id = Uuid::uuid4()->toString();
            $check->auditId = $this->audit->id;
            $check->checklistId = $this->checklist->id;
            $check->checkpointId = null;
            if ($objectType == 'section') {
                $check->sectionId = $section;
            } else {
                $check->sectionId = null;
            }
            $check->parentType = $objectType;
            $check->parentId = $section;
            $check->valueSchemeId = null;
            $check->scoringSchemeId = null;
            $check->evaluationSchemeId = null;
            $check->objectType = $object->objectType;
            $check->objectId = $object->objectId;
            $check->save();
        } else if ($entry->objectType == 'checkpoint') {
            if (in_array($entry->objectId, $this->selectedCheckpoints)) {
                $object = Checkpoint::findOrFail($entry->objectId);

                $check = new Check();
                $check->id = Uuid::uuid4()->toString();
                $check->auditId = $this->audit->id;
                $check->checklistId = $this->checklist->id;
                $check->checkpointId = $object->id;
                $check->parentType = $objectType;
                $check->parentId = $section;
                if ($objectType == 'section') {
                    $check->sectionId = $section;
                } else {
                    $check->sectionId = null;
                }
                $check->valueSchemeId = null;
                $check->evaluationSchemeId = $object->evaluationSchemeId;
                $check->scoringSchemeId = $object->scoringSchemeId;
                $check->objectType = 'checkpoint';
                $check->objectId = $object->id;
                $check->save();

                if (!is_null($object->entries)) {
                    foreach ($object->entries as $entry) {
                        $this->handleChecklistEntry($entry, $object->id, 'checkpoint');
                    }
                }
            }
        } else {
            return response('', Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        return true;
    }
}
