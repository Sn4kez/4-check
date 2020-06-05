<?php

namespace App;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\Rule;
use RuntimeException;

class Checkpoint extends Model
{
    use SoftDeletes, Uuid;

    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    const DELETED_AT = 'deletedAt';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'checkpoints';

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
        'prompt',
        'description',
        'mandatory',
        'factor',
        'index',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'mandatory' => 'boolean',
        'factor' => 'float',
    ];

    /**
     * Returns the validation rules for the model.
     *
     * @param string $action
     * @param array $merge
     * @param string $id
     * @return array
     */
    public static function rules($action, $merge = [], $id = null)
    {
        if (!in_array($action, ['create', 'update'])) {
            throw new RuntimeException('Unknown action: ' . $action);
        }
        $rules = [
            'prompt' => 'required|string|max:128',
            'description' => 'nullable|string',
            'scoringSchemeId' => 'required|string|exists:scoring_schemes,id',
            'mandatory' => 'boolean',
            'factor' => 'numeric',
            'index' => 'required|string|max:128',
        ];
        if ($action == 'create') {
            $rules = array_merge($rules, [
                'evaluationScheme.type' => ['required', Rule::in(['choice', 'value'])],
                'evaluationScheme.data' => 'required|array',
            ]);
        }
        if ($action == 'update') {
            $rules = array_merge($rules, [
                'prompt' => 'sometimes|' . $rules['prompt'],
                'description' => 'sometimes|' . $rules['description'],
                'scoringSchemeId' => 'sometimes|' . $rules['scoringSchemeId'],
                'index' => 'sometimes|' . $rules['index'],
            ]);
        }
        return array_merge($rules, $merge);
    }

    /**
     * Get the checkpoint's entries.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function entries()
    {
        return $this->morphMany(
            ChecklistEntry::class,
            'parent',
            'parentType',
            'parentId');
    }

    /**
     * Get the checkpoint's parent entry.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function parentEntry()
    {
        return $this->morphOne(
            ChecklistEntry::class,
            'object',
            'objectType',
            'objectId');
    }

    /**
     * Get the checkpoint's scoring scheme.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function scoringScheme()
    {
        return $this->belongsTo(ScoringScheme::class, 'scoringSchemeId');
    }

    /**
     * Get the checkpoint's evaluation scheme.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function evaluationScheme()
    {
        return $this->morphTo(
            'evaluationScheme',
            'evaluationSchemeType',
            'evaluationSchemeId');
    }

    /**
     * Adds an object as a new extension entry.
     *
     * @param TextfieldExtension|LocationExtension|ParticipantExtension $object
     * @return Extension
     */
    public function extension($object)
    {
        $extension = new Extension();
        $extension->object()->associate($object);
        return $extension;
    }

    /**
     * Adds an object as a new checklist entry.
     *
     * @param Section|Checkpoint|Extension $object
     * @return ChecklistEntry
     */
    public function entry($object)
    {
        $entry = new ChecklistEntry();
        $entry->parent()->associate($this);
        $entry->object()->associate($object);
        return $entry;
    }
}
