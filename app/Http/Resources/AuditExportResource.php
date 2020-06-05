<?php

namespace App\Http\Resources;

use App\Check;
use App\ChecklistEntry;
use App\Directory;
use App\Checklist;
use App\Http\Resources\ChecklistEntryResource;
use App\ChoiceCheck;
use App\Media;
use App\PictureCheck;
use App\Section;
use App\Company;
use App\LocationCheck;
use App\DirectoryEntry;
use App\CorporateIdentity;
use App\Address;
use App\Location;
use App\Checkpoint;
use App\TextfieldCheck;
use App\TextfieldExtension;
use App\User;
use App\Score;
use App\ValueCheck;
use App\ParticipantCheck;
use App\PictureExtension;
use App\Extension;
use Carbon\Carbon;
use Auth;
/**
 * @property string $id
 * @property \App\Company $company
 * @property \App\User $user
 * @property \App\Checklist $checklist
 * @property \App\AuditState $state
 * @property \Illuminate\Support\Carbon $createdAt
 * @property \Illuminate\Support\Carbon $updatedAt
 * @property \Illuminate\Support\Carbon $deletedAt
 */
class AuditExportResource extends Resource {
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request) {
        $logo4check = 'data:image/png;base64,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';

        $checks = Check::where('auditId', '=', $this->id)->where('objectType','=','checkpoint')->get();
        $checklist = Checklist::find($this->checklistId);
        $company  = Company::find($this->companyId);
        $date = Carbon::now();
        $ci = CorporateIdentity::where('companyId', '=', $this->companyId)->first();
        $user = Auth::user();
        $address = Address::where('companyId', '=', $this->companyId)->orderBy('typeId', 'desc')->first();
        $country = null;
        $locationChecks = Check::where('auditId', '=', $this->id)->where('objectType', '=', 'location')->get();
        $pictureChecks = Check::where('auditId', '=', $this->id)->where('valueType', '=', 'picture')->whereNull('parentType')->get();
        $locations = [];
        $creator = User::find($this->userId);
        $directoryEntry = DirectoryEntry::where('objectType', '=', 'checklist')->where('objectId', '=', $checklist->id)->first();
        $participantEntries = Check::where('auditId', '=', $this->id)->where('valueType', '=', 'participant')->get();

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

        $creationAt = $this->executionAt;

        if(is_null($creationAt)) {
            $creationAt = $this->updatedAt;
        }

        $creationAt = new Carbon($creationAt);

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

        $result = [];
        $rowNumber = 1;
        $imagesOne = [];
        $imagesTwo = [];
        $signaturesOne = [];
        $signaturesTwo = [];
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

            $textfieldCheck = Check::where('auditId', '=', $this->id)->where('objectType', '=', 'textfield')->where('parentType', '=', 'checkpoint')->where('parentId', '=', $check->checkpointId)->first();

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

            $images = Check::where('auditId', '=', $this->id)->where('valueType', '=', 'picture')->where('parentType', '=', 'checkpoint')->where('parentId', '=', $check->checkpointId)->get();

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
                "question" => is_null($checkpoint) ? '' : $checkpoint->prompt,
                "description" => is_null($description) ? '' : $description,
                "note" => is_null($note) ? '' : $note,
                "factor" => is_null($checkpoint) ? '' : $checkpoint->factor,
                "rating" => is_null($rating) ? '' : $rating,
                "id" => $rowNumber
            ];

            $rowNumber++;
        }

        foreach($pictureChecks as $check) {
            $pictureCheck = PictureCheck::find($check->valueId);
            $pictureExtension = PictureExtension::find($check->objectId);
                
                $path = Media::getPath($pictureCheck->value);
                $base64Content = Media::getBase64String($path);
                
                if($pictureExtension->type == 'signature') {
                    
                    if($sigCounter % 2 == 0) {
                        $signaturesOne[] = [
                            'id' => $rowNumber,
                            'entry' => $base64Content
                        ];
                    } else {
                        $signaturesTwo[] = [
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

        return [
            'checklist' => [
                'creation_date' => $creationAt->format('d.m.Y'),
                'creator' => $creator->firstName . ' '. $creator->lastName,
                'directory' => $this->getDirectory($directoryEntry->parentId),
                'entries' => $result,
                'locations' => $locations,
                'name' => is_null($checklist) ? '' : $checklist->name,
                'participants' => $participants
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
            'picturesOne' => $imagesOne,
            'picturesTwo' => $imagesTwo,
            'signaturesOne' => $signaturesOne,
            'signaturesTwo' => $signaturesTwo,
            'export_date' => $date->format('d.m.Y'),
            'export_user_name' => $user->firstName . ' ' . $user->lastName,
            'export_version' => '1.0.0',
        ];
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
}
