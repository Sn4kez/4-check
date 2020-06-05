<?php

namespace App\Http\Resources;

use App\Check;
use App\Checkpoint;
use App\Media;
use App\Location;
use App\TextfieldExtension;
use App\LocationExtension;
use App\ParticipantExtension;
use App\PictureExtension;
use App\Extension;
use App\ValueCheck;
use App\ChoiceCheck;
use App\LocationCheck;
use App\PictureCheck;
use App\TextfieldCheck;
use App\ParticipantCheck;
use App\ScoringScheme;
use App\ValueScheme;
use App\ChoiceScheme;
use App\Score;
use App\ScoreCondition;
use Lcobucci\JWT\Signature;

/**
 * @property string $id
 * @property string $audit
 * @property string $checklist
 * @property string $section
 * @property string $checkpoint
 * @property string $valueScheme
 * @property string $scoringScheme
 * @property float $rating
 * @property string $objectType
 * @property string $objectId
 * @property \Illuminate\Support\Carbon $createdAt
 * @property \Illuminate\Support\Carbon $updatedAt
 * @property \Illuminate\Support\Carbon $deletedAt
 */
class CheckResource extends Resource {
    const resourceMap = [
        Checkpoint::class => CheckpointResource::class,
        TextfieldExtension::class => TextfieldExtensionResource::class,
        LocationExtension::class => LocationExtensionResource::class,
        ParticipantExtension::class => ParticipantExtensionResource::class,
        PictureExtension::class => PictureExtensionResource::class,
    ];

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request) {
        if (is_object($this->object)) {
            $objectResource = array_key_exists(get_class($this->object), self::resourceMap) ? self::resourceMap[get_class($this->object)] : null;
        } else {
            $objectResource = null;
        }

        $value = null;
        $additionalFields = [];

        if (!is_null($this->valueId) && !is_null($this->valueType)) {
            if ($this->valueType == 'value') {
                $value = ValueCheck::findOrFail($this->valueId);
            } else if ($this->valueType == 'choice') {
                $value = ChoiceCheck::findOrFail($this->valueId);
            } else if ($this->valueType == 'picture') {
                $value = PictureCheck::findOrFail($this->valueId);
            } else if ($this->valueType == 'textfield') {
                $value = TextfieldCheck::findOrFail($this->valueId);
            } else if ($this->valueType == 'participant') {
                $value = ParticipantCheck::findOrFail($this->valueId);
            } else if ($this->valueType == 'location') {
                $value = LocationCheck::findOrFail($this->valueId);
            }
        }

        $scoringScheme = ScoringScheme::find($this->scoringSchemeId);
        $valueScheme = null;

        if ($this->valueType == 'choice') {
            $valueScheme = ChoiceScheme::find($this->evaluationSchemeId);
        } else if ($this->valueType == 'value') {
            $valueScheme = ValueScheme::find($this->evaluationSchemeId);
        }

        $score = null;
        if (!is_null($value)) {
            $score = Score::find($value->scoreId);
        }
        $conditions = ScoreCondition::where('valueSchemeId', '=', $this->evaluationSchemeId)->get();

        if (count($conditions) == 0) {
            $conditions = null;
        }

        if ($this->valueType === Check::VALUE_TYPE_PICTURE) {
            $mediaName = $value->value;
            $path = Media::getPath($mediaName);
            $base64 = Media::getBase64String($path);
            $additionalFields['base64'] = $base64;
        }

        $object = is_null($objectResource) ? null : $objectResource::make($this->object);

        if ($this->valueType === Check::VALUE_TYPE_LOCATION) {
            if (is_null($object->locationId ?? null)) {
                if ($value !== null) {
                    if (!is_null($value->locationId)) {
                        $this->object->locationId = $value->locationId;
                    }
                }
            }
        }

        $index = null;
        $extension = null;

        if(!is_null($object)) {
            if(is_null($object->index)) {
                $extension = Extension::where('objectId', '=', $this->objectId)->where('objectType', '=', $this->objectType)->first();

                if(!is_null($extension)) {
                    $index = $extension->index;
                }
            } else {
                $index = $object->index;
            }
        }

        $object = is_null($objectResource) ? null : $objectResource::make($this->object);

        $feedback = array_merge([
            'id' => $this->id,
            'audit' => $this->auditId,
            'checklist' => $this->checklistId,
            'section' => $this->sectionId,
            'checkpoint' => $this->checkpointId,
	        'parentId' => $this->parentId,
            'evaluatingSchemeId' => $this->evaluationSchemeId,
            'evaluatingScheme' => $valueScheme,
            'scoringSchemeId' => $this->scoringSchemeId,
            'scoringScheme' => $scoringScheme,
            'score' => $score,
            'index' => $index,
            'conditions' => $conditions,
            'rating' => $this->rating,
            'objectType' => $this->objectType,
            'objectId' => $this->objectId,
            'extensionType' => $object === null ? null : $object->type,
            'valueType' => $this->valueType,
            'valueId' => $this->valueId,
            'object' => $object,
            'value' => $value,
        ], $additionalFields);

	return $feedback;
    }

    public function checkIfValueTypeIsExtension($type) {
        return $type == 'picture' || $type == 'textfield' || $type == 'participant' || $type == 'location';
    }
}
