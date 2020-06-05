<?php

namespace App;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use RuntimeException;

/**
 * @property mixed checkpointId
 */
class Check extends Model {
    use SoftDeletes, Uuid;

    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    const DELETED_AT = 'deletedAt';

    const VALUE_TYPE_VALUE = 'value';
    const VALUE_TYPE_CHOICE = 'choice';
    const VALUE_TYPE_PARTICIPANTS = 'participant';
    const VALUE_TYPE_LOCATION = 'location';
    const VALUE_TYPE_PICTURE = 'picture';
    const VALUE_TYPE_TEXTFIELD = 'textfield';
    const VALUE_TYPE_CHECKPOINT = 'checkpoint';

    const OBJECT_TYPE_PICTURE = 'media';
    const OBJECT_TYPE_SIGNATURE = 'signature';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'checks';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'deletedAt',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'executionDate',
        'rating'
    ];

    public static function rules($action, $merge = [], $id = null) {
        if (!in_array($action, [
            'create',
            'update'
        ])) {
            throw new RuntimeException('Unknown action: ' . $action);
        }
        $rules = [
            'auditId' => 'required|exists:audits,id',
            'checklistId' => 'required|exists:checklists,id',
            'checkpointId' => 'required|exists:checkpoints,id',
            'sectionId' => 'nullable|exists:sections,id',
            'valueSchemeId' => 'nullable|exists:value_schemes,id',
            'scoringSchemeId' => 'nullable|exists:scoring_schemes,id',
            'executionDate' => 'nullable|date',
            'objectType' => 'required|string',
            'objectId' => 'required|string'
        ];
        if ($action == 'update') {
            $rules = array_merge($rules, [
                'auditId' => 'sometimes|' . $rules['auditId'],
                'checklistId' => 'sometimes|' . $rules['checklistId'],
                'checkpointId' => 'sometimes|' . $rules['checkpointId'],
                'sectionId' => 'sometimes|' . $rules['sectionId'],
                'valueSchemeId' => 'sometimes|' . $rules['valueSchemeId'],
                'scoringScheme' => 'sometimes|' . $rules['scoringSchemeId'],
                'executionDate' => 'sometimes|' . $rules['executionDate'],
                'objectType' => 'sometimes|' . $rules['objectType'],
                'objectId' => 'sometimes|' . $rules['objectId']
            ]);
        }
        return array_merge($rules, $merge);
    }

    /**
     * Get the check's object (Value, Choice, Participant, Location, Picture, or Textfield).
     */
    public function object() {
        return $this->morphTo('object', 'objectType', 'objectId');
    }

    /**
     * Get the check's object (Value, Choice, Participant, Location, Picture, or Textfield).
     */
    public function value() {
        return $this->morphTo('value', 'valueType', 'valueId');
    }

    /**
     * @param \App\Check $check
     * @return mixed
     */
    public function getExtensionValue($check) {
        $objectType = $check->objectType;

        switch ($objectType) {
            case self::VALUE_TYPE_CHOICE:
                break;
            case self::VALUE_TYPE_LOCATION:
                break;
            case self::VALUE_TYPE_PARTICIPANTS:
                break;
            case self::VALUE_TYPE_PICTURE:
                $pictureValueObject = PictureCheck::findOrFail($check->valueId);
                return $pictureValueObject->value;
            case self::VALUE_TYPE_TEXTFIELD:
                break;
            case self::VALUE_TYPE_VALUE:
                break;
        }

        return null;
    }

    /**
     * @param Check $check
     * @return null
     */
    public static function getCheckValueObject(Check $check) {
        $objectType = $check->objectType;

        switch ($objectType) {
            case self::VALUE_TYPE_CHOICE:
                return ChoiceCheck::findOrFail($check->valueId);
            case self::VALUE_TYPE_CHECKPOINT:
                return ChoiceCheck::findOrFail($check->valueId);
            case self::VALUE_TYPE_LOCATION:
                return LocationCheck::findOrFail($check->valueId);
            case self::VALUE_TYPE_PARTICIPANTS:
                return ParticipantCheck::findOrFail($check->valueId);
            case self::VALUE_TYPE_PICTURE:
                return PictureCheck::findOrFail($check->valueId);
            case self::VALUE_TYPE_TEXTFIELD:
                return TextfieldCheck::findOrFail($check->valueId);
            case self::VALUE_TYPE_VALUE:
                return ValueCheck::findOrFail($check->valueId);
        }

        return null;
    }

    public static function getSectionName(Check $check) {
        $sectionId = $check->sectionId;

        if (!is_null($sectionId) && strlen($sectionId) > 0) {
            $section = Section::find($sectionId);

            if (!is_null($section)) {
                return $section->title;
            }
        }

        return '';
    }

    /**
     * @return string
     */
    public function getQuestionTitle() {
        $prompt = "";
        $checkpoint = Checkpoint::where("id", "=", $this->checkpointId)->first();

        if (!is_null($checkpoint)) {
            $prompt = $checkpoint->prompt;
        }

        return $prompt;
    }

    public function getCheckpoint() {
        return Checkpoint::where("id", "=", $this->checkpointId)->first();
    }
}
