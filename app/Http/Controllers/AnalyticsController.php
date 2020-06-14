<?php

namespace App\Http\Controllers;

use App\AuditState;
use App\Check;
use App\Checklist;
use App\ChoiceCheck;
use App\DirectoryEntry;
use App\Http\Resources\AuditResource;
use App\Http\Resources\CheckResource;
use App\Http\Resources\UserResource;
use App\Location;
use App\LocationCheck;
use App\PictureCheck;
use App\Score;
use App\User;
use App\ValueCheck;
use Carbon\Carbon;
use Illuminate\Http\Request;
use \Illuminate\Http\Response;
use App\Audit;
use App\Media;
use App\Company;
use App\CorporateIdentity;
use App\Address;
use App\Directory;
use App\ParticipantCheck;
use App\TextfieldCheck;
use App\PictureExtension;
use App\Section;
use App\Checkpoint;
use App\ChecklistEntry;
use App\Extension;
use App\TextfieldExtension;
use GuzzleHttp\Client;

class AnalyticsController extends Controller {
    const PARAM_DATE_FROM = 'start';
    const PARAM_DATE_TO = 'end';
    const PARAM_DIRECTORY = 'directory';
    const PARAM_CHECKLIST = 'checklist';
    const PARAM_LOCATION = 'location';

    const MEDIA_TYPE_NAME = 'picture';

    const CHART_KEY_UNGROUPED = 'ungrouped';
    const CHART_KEY_UNGROUPED_FACTOR = 'ungrouped_factor';

    const CHART_KEY_GROUPED = 'grouped';
    const CHART_KEY_GROUPED_FACTOR = 'grouped_factor';

    const CHART_KEY_NO_SECTION = 'no_section';
    const CHART_KEY_NO_SECTION_FACTOR = 'no_section_factor';

    const CHART_KEY_CHART_ONE = 'chart_one_average_value_of_all_choices';

    const CHART_KEY_CHART_TWO = 'chart_two_count_of_yes_and_no';

    const CHART_KEY_CHART_THREE = 'chart_three_average_values_of_all_choices_grouped_by_section';

    private $charts;
    private $medias;
    private $audits;
    private $sectionNames = [];
    private $scoreTitleCounts = [];
    private $auditDirectories;
    private $auditDirectoriesChecklistIds;
    private $locationIds;
    private $locationCheckIds;
    private $locationAuditIds;

    public function index(Request $request) {
        $companyAudits = Audit::where('companyId', $request->user()->company->id);
        $loadedAudits = $this->loadAudits($request, $companyAudits);
	    $this->audits = $this->filterAudits($request, $loadedAudits);

        $this->setMediaData();
        $this->setChartData();

        $returnData = [
            'charts' => $this->charts,
            'audits' => AuditResource::collection($this->audits->get()),
            'media' => $this->medias
        ];

        return response($returnData, Response::HTTP_OK);
    }

    public function export(Request $request) {
        $logo4check = 'data:image/png;base64,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';

        $companyAudits = Audit::where('companyId', $request->user()->company->id);
        $loadedAudits = $this->loadAudits($request, $companyAudits);
        $this->audits = $this->filterAudits($request, $loadedAudits);
        $audits = [];

        foreach($this->audits->get() as $audit) {
            $audits[] = $audit->id;
        }

        $checklist = Checklist::find($request->input(self::PARAM_CHECKLIST));
        $company  = Company::find($request->user()->company->id);
        $ci = CorporateIdentity::where('companyId', '=', $company->id)->first();
        $address = Address::where('companyId', '=', $company->id)->orderBy('typeId', 'desc')->first();
        $directoryEntry = DirectoryEntry::where('objectType', '=', 'checklist')->where('objectId', '=', $checklist->id)->first();
        $date = Carbon::now();
        $creator = User::find($checklist->createdBy);
        $creationAt = $checklist->createdAt;

        $checks = Check::whereIn('auditId', $audits)->where('objectType','=','checkpoint')->get();

        $pictureChecks = Check::whereIn('auditId', $audits)->where('valueType', '=', 'picture')->whereNull('parentType')->get();

        $locationChecks = Check::whereIn('auditId', $audits)->where('objectType', '=', 'location')->get();
        $locations = [];

        foreach($locationChecks as $locationCheck) {
            $check = LocationCheck::where('checkId', '=', $locationCheck->id)->first();
            if(!is_null($check)) {
                $data = Location::find($check->locationId);

                $locations[] = [
                    'name' => is_null($data) ? '' : $data->name,
                    'description' => is_null($data) ? '' :  $data->description,
                    'street' => is_null($data) ? '' :  $data->street,
                    'street_number' => is_null($data) ? '' :  $data->streetNumber,
                    'zip' => is_null($data) ? '' :  $data->postalCode,
                    'city' => is_null($data) ? '' :  $data->city,
                    'country' => is_null($data) ? '' :  $this->getCountry($data->countryId)
                ];
            }
         }

        $participantEntries = Check::whereIn('auditId', $audits)->where('valueType', '=', 'participant')->get();

        $participants = '';

        foreach($participantEntries as $participantEntry) {
            $data = ParticipantCheck::find($participantEntry->valueId);

            if(!is_null($data->externalParticipant)) {
                if(strlen($participants) > 0) {
                    $participants .=', ';
                }
                $participants .= $data->externalParticipant;
            }
            if(!is_null($data->participantId)) {
                if(strlen($participants) > 0) {
                    $participants .=', ';
                }

                $participantUser = User::find($data->participantId);

                $participants .= $participantUser->firstName . ' ' . $participantUser->lastName;
            }
        }

        $result = [];
        $rowNumber = 1;
        $imagesOne = [];
        $imagesTwo = [];
        $signatureOne = [];
        $signatureTwo = [];
        $counter = 0;
        $sigCounter = 0;

        foreach($checks as $check) {
            $checkpoint = Checkpoint::find($check->checkpointId);
            $section = Section::find($check->sectionId);
            $value = null;

            if (!is_null($check->valueId) && !is_null($check->valueType)) {
                if ($check->valueType == 'value') {
                    $value = ValueCheck::findOrFail($check->valueId);
                } else if ($check->valueType == 'choice') {
                    $value = ChoiceCheck::findOrFail($check->valueId);
                }
            }

            $score = null;

            if (!is_null($value)) {
                $score = Score::find($value->scoreId);
            }

            $rating = null;

            if(!is_null($score)) {
                $rating = $score->value . " (" . $score->name . ")";
            }

            $description = null;
            $note = null;
            $pictures = [];

            $textfieldCheck = Check::whereIn('auditId', $audits)->where('objectType', '=', 'textfield')->where('parentType', '=', 'checkpoint')->where('parentId', '=', $check->checkpointId)->first();

            if($textfieldCheck != null) {
                if($textfieldCheck->valueType === 'textfield') {
                    $text= TextfieldCheck::find($textfieldCheck->valueId);
                    $note = $text->value;
                }
            }

            $extensions  = ChecklistEntry::where('parentType', '=', 'checkpoint')->where('parentId', '=', $check->checkpointId)->where('objectType', '=', 'extension')->get();

            foreach($extensions as $extension) {
                $ext = Extension::find($extension->objectId);

                if ($ext->objectType == 'textfield') {
                    $textfield = TextfieldExtension::find($ext->objectId);

                    if($textfield->fixed == 1) {
                        $description = $textfield->text;
                    }
                }
            }

            $images = Check::whereIn('auditId', $audits)->where('valueType', '=', 'picture')->where('parentType', '=', 'checkpoint')->where('parentId', '=', $check->checkpointId)->get();

            foreach($images as $image) {
                $pictureCheck = PictureCheck::find($image->valueId);

                $pictureExtension = PictureExtension::find($image->objectId);
                $path = Media::getPath($pictureCheck->value);
                $base64Content = Media::getBase64String($path);
                

                if($pictureExtension->type == 'signature') {
                    
                    if($sigCounter % 2 == 0) {
                        $signatureOne[] = [
                            'id' => $rowNumber,
                            'entry' => $base64Content
                        ];
                    } else {
                        $signatureTwo[] = [
                            'id' => $rowNumber,
                            'entry' => $base64Content
                        ];
                    }
                    $sigCounter++;
                } else {
                    if($counter % 2 == 0) {
                        $imagesOne[] = [
                            'id' => $rowNumber,
                            'entry' => $base64Content
                        ];
                    } else {
                        $imagesTwo[] = [
                            'id' => $rowNumber,
                            'entry' => $base64Content
                        ];
                    }
                    $counter++;
                }
                
            }
            
            $result[] = [
                "group" => is_null($section) ? '' : $section->title,
                "date" => $check->updatedAt->format('d.m.Y'),
                "question" => is_null($checkpoint) ? '' : $checkpoint->prompt,
                "description" => is_null($description) ? '' : $description,
                "note" => is_null($note) ? '' : $note,
                "factor" => is_null($checkpoint) ? '' : $checkpoint->factor,
                "rating" => is_null($rating) ? '' : $rating,
                "id" => $rowNumber
            ];

            $rowNumber++;
        }

        foreach($pictureChecks as $image) {
            $pictureCheck = PictureCheck::find($image->valueId);
            $pictureExtension = PictureExtension::find($image->objectId);
            $path = Media::getPath($pictureCheck->value);
            $base64Content = Media::getBase64String($path);


            if($pictureExtension->type === 'signature') {
                if($sigCounter % 2 == 0) {
                    $signatureOne[] = [
                        'id' => '-',
                        'entry' => $base64Content
                    ];
                } else {
                    $signatureTwo[] = [
                        'id' => '-',
                        'entry' => $base64Content
                    ];
                }
                $sigCounter++;
            } else {
                if($counter % 2 == 0) {
                    $imagesOne[] = [
                        'id' => '-',
                        'entry' => $base64Content
                    ];
                } else {
                    $imagesTwo[] = [
                        'id' => '-',
                        'entry' => $base64Content
                    ];
                }
                $counter++;
            }
        }

        
        $this->setChartData();
        $creatorName = '';

        if(is_object($creator)) {
            $creator->firstName . ' '. $creator->lastName;
        }

        $returnData = [
            'chartAvgAll' => $this->getAverageOverChecks(),
            'chartCountOptions' => $this->getOptionsCount(),
            'chartGroupedBySection' => $this->getAverageGrouped(),
            'checklist' => [
                'creation_date' => $creationAt->format('d.m.Y'),
                'creator' => $creatorName,
                'locations' => $locations,
                'name' => is_null($checklist) ? '' : $checklist->name,
                'participants' => $participants,
                'entries' => $result
            ],
            'company' => [
                'name' => is_null($company) ? '' : $company->name,
                'logo' => is_null($ci) || is_null($ci->image)  ? $logo4check : $ci->image,
                'street' => is_null($address) ? '' : $address->line1,
                'street_number' => is_null($address) ? '' : $address->line2,
                'zip' => is_null($address) ? '' : $address->postalCode,
                'city' => is_null($address) ? '' : $address->City,
                'country' => is_null($address) ? '' : $this->getCountry($address->countryId),
            ],
            'images1' => $imagesOne,
            'images2' => $imagesTwo,
            'signatures1' => $signatureOne,
            'signatures2' => $signatureTwo,
            'directory' => $this->getDirectory($directoryEntry->parentId),
            'export_date' => $date->format('d.m.Y'),
            'export_user_name' => $request->user()->firstName . ' ' . $request->user()->lastName,
            'export_version' => '1.0.0'
        ];

        //ToDo: add pdf generator resource for multiple export

        $key = env('PDF_GENERATOR_KEY');
        $workspace = env('PDF_GENERATOR_WORKSPACE');
        $secret = env('PDF_GENERATOR_SECRET');
        $resource = '';

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
            'body' => json_encode($returnData),
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
        
        return Response($contents, Response::HTTP_OK);
    }

    private function setMediaData() {
        $this->medias = [];

        foreach ($this->audits->get() as $audit) {
            $checks = Check::where('auditId', '=', $audit->id)->where('objectType', '=', self::MEDIA_TYPE_NAME);

            if ($checks->count() > 0) {
                foreach ($checks->get() as $check) {
                    $pictureValueObject = PictureCheck::find($check->valueId);
                    $checkResource = CheckResource::make($check);
		    $checkAdditional = collect($checkResource);
		    $checkResource->extensionType = $checkAdditional->get("extensionType");

                    if (!is_null($pictureValueObject)) {
                        $mediaName = $pictureValueObject->value;
                        $created = $pictureValueObject->createdAt;
                        $base64Content = '';

                        if (!is_null($mediaName) && strlen($mediaName) > 0) {
                            $path = Media::getPath($mediaName);
                            $base64Content = Media::getBase64String($path);
                        }

                        /** @var User $user */
                        $user = $audit->user;
                        $username = trim(sprintf("%s %s %s", $user->firstName, $user->middleName, $user->lastName));

                        $this->medias[] = [
                            'audit_id' => $audit->id,
                            'user_id' => $user->id,
                            'user_name' => $username,
                            'user_email' => $user->email,
                            'base64' => $base64Content,
                            'extension_type' => $checkResource === null ?  null : $checkResource->extensionType ?? null,
                            'created' => $created,
                            'created_simple' => date('Y-m-d H:i:s', strtotime($created))
                        ];
                    }
                }
            }
        }
    }

    private function setChartData() {
        $this->charts = [
            self::CHART_KEY_UNGROUPED => [],
            self::CHART_KEY_UNGROUPED_FACTOR => 0,
            self::CHART_KEY_GROUPED => [self::CHART_KEY_NO_SECTION => []],
            self::CHART_KEY_GROUPED_FACTOR => [self::CHART_KEY_NO_SECTION => 0]
        ];

        foreach ($this->audits->get() as $audit) {
            $checks = Check::where('auditId', '=', $audit->id)->whereIn('objectType', [
                Check::VALUE_TYPE_CHOICE,
                Check::VALUE_TYPE_CHECKPOINT,
                Check::VALUE_TYPE_VALUE
            ]);

            if ($checks->count() > 0) {
                foreach ($checks->get() as $check) {
                    $objectType = $check->objectType;
                    $sectionName = Check::getSectionName($check);
                    $sectionId = $check->sectionId;
                    $factor = $check->object->factor ?? 1.0;

                    if ($factor === 0) {
                        $factor = 1;
                    }

                    $this->cacheSectionNameIfSet($sectionId, $sectionName);

                    switch ($objectType) {
                        case Check::VALUE_TYPE_CHOICE:
                        case Check::VALUE_TYPE_CHECKPOINT:
                        case Check::VALUE_TYPE_VALUE:
                            $valueId = $check->valueId;
                            $scoreId = null;

                            if (!is_null($valueId)) {
                                $valueCheck = ChoiceCheck::find($valueId) ?? (ValueCheck::find($valueId) ?? null);

                                if (!is_null($valueCheck)) {
                                    $scoreId = $valueCheck->scoreId;
                                }
                            }

                            if (!is_null($scoreId) && strlen($scoreId) > 0) {
                                $score = Score::find($scoreId);

                                if (!is_null($score)) {
                                    $this->sumUpScoreTitles($score);
                                    $this->sumUpUngroupedScoreValue($score, $factor);
                                    $this->sumUpGroupedScoreValue($sectionName, $sectionId, $score, $factor);
                                }
                            }

                            break;
                    }
                }
            }
        }

        $this->calculateChartOne();
        $this->calculateChartTwo();
        $this->calculateChartThree();
    }

    private function cacheSectionNameIfSet($sectionId, $sectionName) {
        if (strlen($sectionName) > 0) {
            if (!array_key_exists($sectionId, $this->sectionNames)) {
                $this->sectionNames[$sectionId] = $sectionName;
            }
        }
    }

    /**
     * @param Score $score
     * @param $factor
     */
    private function sumUpUngroupedScoreValue(Score $score, $factor) {
        $scoreValue = $score->value;

        $this->charts[self::CHART_KEY_UNGROUPED][] = round($scoreValue * $factor, 2);
        $this->charts[self::CHART_KEY_UNGROUPED_FACTOR] += $factor;
    }

    /**
     * @param string $sectionName
     * @param string|null $sectionId
     * @param Score $score
     * @param $factor
     */
    private function sumUpGroupedScoreValue(string $sectionName, $sectionId, Score $score, $factor) {
        $scoreValue = round($score->value * $factor, 2);

        if (strlen($sectionName) > 0) {
            $this->charts[self::CHART_KEY_GROUPED][$sectionId][] = $scoreValue;

            if (!array_key_exists($sectionId, $this->charts[self::CHART_KEY_GROUPED_FACTOR])) {
                $this->charts[self::CHART_KEY_GROUPED_FACTOR][$sectionId] = 0;
            }

            $this->charts[self::CHART_KEY_GROUPED_FACTOR][$sectionId] += $factor;
        } else {
            $this->charts[self::CHART_KEY_GROUPED][self::CHART_KEY_NO_SECTION][] = $scoreValue;
            $this->charts[self::CHART_KEY_GROUPED_FACTOR][self::CHART_KEY_NO_SECTION] += $factor;
        }
    }

    /**
     * @param Score $score
     */
    private function sumUpScoreTitles(Score $score) {
        $scoreName = $score->name;

        if (!array_key_exists($scoreName, $this->scoreTitleCounts)) {
            $this->scoreTitleCounts[$scoreName] = 0;
        }

        $this->scoreTitleCounts[$scoreName] += 1;
    }

    private function calculateChartOne() {
        $scoreAvg = 0;

        if (count($this->charts[self::CHART_KEY_UNGROUPED]) > 0) {
            $countAll = $this->charts[self::CHART_KEY_UNGROUPED_FACTOR];
            $scoreSum = array_sum($this->charts[self::CHART_KEY_UNGROUPED]);
            $scoreAvg = round($scoreSum / $countAll, 2);
        }

        $this->charts[self::CHART_KEY_CHART_ONE] = [
            'labels' => [
                'Ergebnis',
            ],
            'datasets' => [
                (object)[
                    'data' => [
                        $scoreAvg
                    ]
                ]
            ]
        ];
    }

    private function calculateChartTwo() {
        $this->charts[self::CHART_KEY_CHART_TWO] = [
            'labels' => array_keys($this->scoreTitleCounts),
            'datasets' => [[]]
        ];

        foreach ($this->scoreTitleCounts as $key => $value) {
            $this->charts[self::CHART_KEY_CHART_TWO]['datasets'][0]['data'][] = $value;
        }

        $this->charts[self::CHART_KEY_CHART_TWO]['datasets'][0] = (object)$this->charts[self::CHART_KEY_CHART_TWO]['datasets'][0];
    }

    private function calculateChartThree() {
        $this->charts[self::CHART_KEY_CHART_THREE] = [
            'labels' => array_merge($this->sectionNames, count($this->charts[self::CHART_KEY_GROUPED][self::CHART_KEY_NO_SECTION]) > 0 ? ['all'] : []),
            'datasets' => [[]]
        ];

        if (count($this->sectionNames) > 0) {
            foreach ($this->sectionNames as $sectionId => $sectionName) {
                $resultAverage = array_key_exists($sectionId, $this->charts[self::CHART_KEY_GROUPED]) ? $this->calculateResultsAverage($this->charts[self::CHART_KEY_GROUPED][$sectionId], $this->charts[self::CHART_KEY_GROUPED_FACTOR][$sectionId]) : 0;
                $this->charts[self::CHART_KEY_CHART_THREE]['datasets'][0]['data'][] = $resultAverage;
            }
        }

        if (count($this->charts[self::CHART_KEY_GROUPED][self::CHART_KEY_NO_SECTION]) > 0) {
            $resultAverage = $this->calculateResultsAverage($this->charts[self::CHART_KEY_GROUPED][self::CHART_KEY_NO_SECTION], $this->charts[self::CHART_KEY_GROUPED_FACTOR][self::CHART_KEY_NO_SECTION]);
            $this->charts[self::CHART_KEY_CHART_THREE]['datasets'][0]['data'][] = $resultAverage;
        }

        $this->charts[self::CHART_KEY_CHART_THREE]['datasets'][0] = (object)$this->charts[self::CHART_KEY_CHART_THREE]['datasets'][0];
    }

    private function calculateResultsAverage($results, $factors) {
        $resultSum = array_sum($results);
        $resultAverage = round($resultSum / $factors, 2);

        return $resultAverage;
    }

    private function loadAudits(Request $request, $audits) {
        $directory = $request->input(self::PARAM_DIRECTORY);
        $checklist = $request->input(self::PARAM_CHECKLIST);

        if (!is_null($directory)) {
            $this->auditDirectories = [$directory];
            $this->auditDirectoriesChecklistIds = [];
            $this->loadOtherDirectories($directory);

            $audits = $audits->whereIn('checklistId', $this->auditDirectoriesChecklistIds);
        } elseif (!is_null($checklist)) {
            $audits = $audits->where('checklistId', '=', $checklist);
        }

        $audits = $audits->where('stateId', '=', AuditState::getFinishedStateId());

        return $audits;
    }

    private function loadOtherDirectories($directoryId) {
        $entries = DirectoryEntry::where('parentId', '=', $directoryId)->where('objectType', '=', 'checklist');

        if ($entries->count() > 0) {
            foreach ($entries->get() as $entry) {
                $this->auditDirectoriesChecklistIds[] = $entry->objectId;
            }
        }

        $allChildren = DirectoryEntry::where('parentId', '=', $directoryId)->where('objectType', '=', 'directory');

        foreach ($allChildren->get() as $directoryEntry) {
            $this->auditDirectories[] = $directoryEntry->objectId;

            $this->loadOtherDirectories($directoryEntry->objectId);
        }
    }

    private function convertDateOrTimestamp($timestampOrDate, $endTime = "00:00:00") {
        if (strlen($timestampOrDate) > 0) {
            if (strpos($timestampOrDate, "-") === false) {
                return date(sprintf("Y-m-d %s", $endTime), $timestampOrDate);
            } else {
                return date(sprintf("Y-m-d %s", $endTime), strtotime($timestampOrDate));
            }
        }

        return date("Y-m-d", time());
    }

    /**
     * Filters audits by date
     *
     * @param $request
     * @param $audits
     * @return mixed
     */
    private function filterAudits(Request $request, $audits) {
        $start = $request->input(self::PARAM_DATE_FROM);
        $end = $request->input(self::PARAM_DATE_TO);
        $locationId = $request->input(self::PARAM_LOCATION);

        if (!is_null($start) && !is_null($end)) {
	    $startNew = $this->convertDateOrTimestamp($start);
	    $endNew = $this->convertDateOrTimestamp($end, "23:59:59");
	    $checkColumn = "updatedAt"; // before: executionAt

            $audits = $audits->whereBetween($checkColumn, [
                $startNew,
                $endNew
            ]);
        }

        if (!is_null($locationId)) {
            $this->locationIds = [];
            $this->findAllLocations($locationId);

            $locationChecks = LocationCheck::whereIn('locationId', $this->locationIds);
            $this->locationCheckIds = [];
            $this->locationAuditIds = [];

            if ($locationChecks->count() > 0) {
                foreach ($locationChecks->get() as $locationCheck) {
                    $this->locationCheckIds[] = $locationCheck->checkId;
                }
            }

            if (count($this->locationCheckIds) > 0) {
                $checks = Check::whereIn('id', $this->locationCheckIds);

                if ($checks->count() > 0) {
                    foreach ($checks->get() as $check) {
                        $this->locationAuditIds[] = $check->auditId;
                    }
                }
            }

            if (count($this->locationAuditIds) > 0) {
                $audits = $audits->whereIn('id', $this->locationAuditIds);
            }
        }

        return $audits;
    }

    private function findAllLocations($parentLocationId) {
        $this->locationIds[] = $parentLocationId;

        $children = Location::where('parentId', '=', $parentLocationId);

        if ($children->count() > 0) {
            foreach ($children->get() as $child) {
                $this->findAllLocations($child->id);
            }
        }
    }

    private function getCountry($code)
    {
        switch($code) {
            case 'de':
                $country = 'Deutschland';
                break;
            case 'at':
                $country = 'Österreich';
                break;
            case 'ch':
                $country = 'Schweiz';
                break;
            default:
                $country = null;
                break;
        }

        return $country;
    }

    private function getDirectory($id) {
        $directory = Directory::find($id);
        $directoryEntry = DirectoryEntry::where('objectType', '=', 'directory')->where('objectId', '=', $id)->first();

        if(is_null($directoryEntry) || is_null($directoryEntry->parentId)) {
            return $directory->name;
        }

        return $directory->name ."/". $this->getDirectory($directoryEntry->parentId);
    }

    private function getAverageOverChecks() {
        return [
                [
                    "label" => "Ergebnis",
                    "data" => $this->charts['chart_one_average_value_of_all_choices']['datasets'][0]->data[0],
                ]
            ];
    }

    private function getOptionsCount() {
        $chartCountOptions = [];
        $data = [];
        
        $labels = $this->charts['chart_two_count_of_yes_and_no']['labels'];
        if(property_exists($this->charts['chart_two_count_of_yes_and_no']['datasets'][0], 'data')) {
            $data = $this->charts['chart_two_count_of_yes_and_no']['datasets'][0]->data;
        }


        for ($i = 0; $i < count($labels); $i++) {
            $chartCountOptions[] = [
                "label" => $labels[$i],
                "data" => $data[$i]
            ];
        }
        return $chartCountOptions;
    }

    private function getAverageGrouped() {
        $chartGroupedBySection = [];

        $data = [];

        $labels = (array) $this->charts['chart_three_average_values_of_all_choices_grouped_by_section']['labels'];
        if(property_exists($this->charts['chart_three_average_values_of_all_choices_grouped_by_section']['datasets'][0], 'data')) {
            $data = $this->charts['chart_three_average_values_of_all_choices_grouped_by_section']['datasets'][0]->data;
        }


        
        $i = 0;
        foreach($labels as $label => $name) {
            $chartGroupedBySection[] = [
                "label" => $name,
                "data" => $data[$i]
            ];
            $i++;
        }
        return $chartGroupedBySection;
    }
}
