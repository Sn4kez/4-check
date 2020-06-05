<?php

namespace App\Http\Controllers;

use App\Audit;
use App\AuditState;
use App\Checklist;
use App\Checkpoint;
use App\ChoiceCheck;
use App\Company;
use App\DirectoryEntry;
use App\Http\Resources\CheckResource;
use App\Http\Resources\PictureExtensionResource;
use App\Location;
use App\LocationCheck;
use App\ParticipantCheck;
use App\PictureCheck;
use App\ScoringScheme;
use App\Section;
use App\TextfieldCheck;
use App\User;
use App\Check;
use App\ValueCheck;
use Illuminate\Http\Request;

class AuditExportController extends Controller {

    const PARAM_NAME_CHECKLIST = "checklist_id";
    const PARAM_NAME_DIRECTORY = "directory_id";

    /**
     * @var bool|resource
     */
    private $fileHandle;

    /** @var int */
    private $processedAuditRows = 0;

    public function index(Request $request) {
        $filterChecklist = $request->input(self::PARAM_NAME_CHECKLIST) ?? null;
        $filterDirectory = $request->input(self::PARAM_NAME_DIRECTORY) ?? null;
        $checklistIds = [];

        /** @var User $user */
        $user = $request->user();

        $this->createCsv();

        $audits = Audit::where("companyId", "=", $user->companyId);

        if (!is_null($filterChecklist)) {
            $checklistIds[] = $filterChecklist;
        }

        if (!is_null($filterDirectory)) {
            $checklists = Checklist::where("parentId", "=", $filterDirectory)->get();

            /** @var Checklist $checklist */
            foreach ($checklists as $checklist) {
                $checklistIds[] = $checklist->id;
            }
        }

        if (count($checklistIds) > 0) {
            $audits->whereIn("checklistId", $checklistIds);
        }

        $allAudits = $audits->get();

        if (count($allAudits) > 0) {
            /** @var Audit $audit */
            foreach ($allAudits as $audit) {
                $this->processAudit($audit);
            }
        }

        die();
    }

    /**
     * Read all data and put it in the CSV
     *
     * @param Audit $audit
     */
    private function processAudit(Audit $audit) {
        $auditId = $audit->id;
        $checklistId = $audit->checklistId;

        /** @var Check[] $checks */
        $checks = Check::where("auditId", "=", $auditId)->get();

        /** @var Checklist $checklist */
        $checklist = Checklist::where("id", "=", $checklistId)->firstOrFail();

        $checklistName = $checklist->name;
        $checklistDescription = $checklist->description;
        $checklistCreatedAt = $checklist->createdAt;
        $checklistUpdatedAt = $checklist->updatedAt;

        /** @var User $createdByUser */
        $createdByUser = $checklist->createdByUser()->first();

        /** @var User $editorUser */
        $editorUser = $checklist->createdByUser()->first();

        $folder = $this->getFolders($checklist);

        $executionDate = $audit->executionAt;

        /** @var User $executionUser */
        $executionUser = $audit->user()->first();

        /** @var AuditState $auditState */
        $auditState = $audit->state()->first();

        /** @var Check $check */
        foreach ($checks as $check) {
            $objectType = $check->objectType;
            $textContent = "";

            $factor = $check->object->factor ?? 1.0;
            $sectionId = $check->sectionId;
            $section = Section::where("id", "=", $sectionId)->first();

            if ($factor === 0) {
                $factor = 1;
            }

            $rating = $check->rating;
            $groupTitle = "";
            $noteContent = "";
            $ratingSystem = "";

            $imageContent = "";
            $locationContent = "";
            $participants = "";

            $signature = false;

            if (!is_null($section)) {
                $groupTitle = $section->title;
            }

            $questionTitle = $check->getQuestionTitle();

            switch ($objectType) {
                case Check::VALUE_TYPE_TEXTFIELD:
                    $questionElement = "Textfeld / Frage";

                    /** @var TextfieldCheck $checkValue */
                    $checkValue = Check::getCheckValueObject($check);
                    $content = $checkValue->value;

                    if (strpos($content, "\r") !== false) {
                        $noteContent = str_replace(["\r", "\n"], ["\\r", "\\n"], $content);
                    } else {
                        $textContent = $content;
                    }

                    break;

                case Check::VALUE_TYPE_LOCATION:
                    $questionElement = "Location";

                    /** @var LocationCheck $locationCheck */
                    $locationCheck = Check::getCheckValueObject($check);

                    /** @var Location $location */
                    $location = Location::find($locationCheck->locationId)->first();
                    $locationContent = sprintf("%s %s, %s %s", $location->street, $location->streetNumber, $location->city, $location->postalCode);

                    break;

                case Check::VALUE_TYPE_PICTURE:
                    $questionElement = "Bild";

                    /** @var TextfieldCheck $checkValue */
                    $checkValue = Check::getCheckValueObject($check);

                    $checkResource = CheckResource::make($check);
                    $checkAdditional = collect($checkResource);
                    $extensionType = $checkAdditional->get("extensionType");

                    if ($extensionType == "signature") {
                        $signature = $checkValue->value;
                    } else {
                        $imageContent = $checkValue->value;
                    }

                    break;

                case Check::VALUE_TYPE_PARTICIPANTS:
                    $questionElement = "Teilnehmer";

                    /** @var ParticipantCheck $checkValue */
                    $checkValue = Check::getCheckValueObject($check);
                    $userId = $checkValue->participantId;
                    $externalUser = $checkValue->externalParticipant;
                    $user = User::find($userId)->first();
                    $username = "";

                    if (!is_null($user)) {
                        $username = trim(sprintf("%s %s", $user->firstName, $user->lastName));
                    }

                    if (strlen($externalUser) > 0) {
                        $participants = sprintf("%s (ext.: %s)", $username, $externalUser);
                    } else {
                        $participants = $username;
                    }

                    break;

                case Check::VALUE_TYPE_CHOICE:
                    $questionElement = "Choice";

                    break;

                case Check::VALUE_TYPE_CHECKPOINT:
                case Check::VALUE_TYPE_VALUE:
                    $questionElement = "Checkpoint";

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
                    }

                    /** @var ScoringScheme $scoringScheme */
                    $scoringScheme = ScoringScheme::find($check->scoringSchemeId)->first();

                    if (!is_null($scoringScheme)) {
                        $ratingSystem = $scoringScheme->name;
                    }

                    break;

                default:
                    $questionElement = $objectType;
            }

            $data = [
                "execution_date" => $executionDate, // 28.06.2019
                "executive" => $executionUser->getName(), // Maier Hans
                "folder" => $folder[0], // Lorem
                "subfolder1" => $folder[1], // Lorem
                "subfolder2" => $folder[2], // Lorem
                "subfolder3" => $folder[3], // Lorem
                "subfolder4" => $folder[4], // Lorem
                "subfolder5" => $folder[5], // Lorem
                "subfolder6" => $folder[6], // Lorem
                "subfolder7" => $folder[7], // Lorem
                "subfolder8" => $folder[8], // Lorem
                "subfolder9" => $folder[9], // Lorem
                "checklist" => $checklistName, // Leistungsverzeichnis Unterhaltsreinigung
                "checklist_description" => $checklistDescription, // Lorem Ipsum
                "audit" => $auditId, // Prüfungsnummer, UUID, 1, 2
                "state" => $auditState->name, // Entwurf, Abgeschlossen, etc. pp
                "group_title" => $groupTitle, // A-5 Verwaltungs- und Büroräume, Empfangsräume
                "question_element" => $questionElement, // Frage
                "question_title" => $questionTitle, // War alles ok?
                "rating_system" => $ratingSystem, // ja/nein
                "rating_factor" => $factor, // 1, 2, ...
                "rating" => $rating, // nein
                "text_content" => $textContent, // 2 x monatliche Vollreinigung
                "note_content" => $noteContent, // dreckig
                "image_content" => $imageContent, // 2345.jpg
                "location_content" => $locationContent, // R123, Mühlenstr. 1
                "participants" => $participants, // Max Meier, Marianne Tester
                "signature" => $signature ? "ja" : "-", // ja oder "-"
                "created_at" => $checklistCreatedAt, // Erstellungsdatum der Checklist, 01.02.2019
                "creator" => $createdByUser->getName(), // Max Mustermann
                "updated_at" => $checklistUpdatedAt, // 01.02.2019
                "editor" => $editorUser->getName(), // Name des Bearbeiters: Marieke Müller
            ];

            if ($this->processedAuditRows === 0) {
                $this->addCsvLine(array_keys($data));
            }

            $this->addCsvLine($data);
            $this->processedAuditRows++;
        }
    }

    /**
     * Creates the csv in memory
     */
    private function createCsv() {
        #$this->fileHandle = fopen("php://memory", "w");
        $path = sprintf("%s/tmp/%s.csv", base_path(), date("YmdHis"));
        $path = sprintf("%s/tmp/test.csv", base_path());
        @mkdir(dirname($path), 0777, true);
        $this->fileHandle = fopen($path, "w");
    }

    /**
     * Adds a line to the csv
     *
     * @param $data
     */
    private function addCsvLine($data) {
        fputcsv($this->fileHandle, $data);
    }

    /**
     * Finishes the export and starts the download
     */
    private function finishAndDownloadFile() {
        $filename = sprintf("audit_export_%s.csv", date("YmdHis"));

        fseek($this->fileHandle, 0);
        header('Content-Type: application/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '";');
        header('HTTP/1.1 200 OK');
        fpassthru($this->fileHandle);
    }

    private function getFolders(Checklist $checklist) {
        $feedback = [];

        for ($i = 0; $i < 10; $i++) {
            $feedback[$i] = "";
        }

        $paths = $checklist->getPaths();

        if (count($paths) > 0) {
            $x = 0;

            foreach ($paths as $path) {
                $feedback[$x] = $path;
                $x++;
            }
        }

        return $feedback;
    }
}
