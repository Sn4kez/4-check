<?php

namespace App;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use RuntimeException;

class Section extends Model
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
    protected $table = 'sections';

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
        'title',
        'index',
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
            'title' => 'required|string|max:128',
            'index' => 'required|string|max:128',
        ];
        if ($action == 'update') {
            $rules = array_merge($rules, [
                'title' => 'sometimes|' . $rules['title'],
                'index' => 'sometimes|' . $rules['index'],
            ]);
        }
        return array_merge($rules, $merge);
    }

    /**
     * Get the section's parent entry.
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
     * Get the section's entries.
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
     * @param Checkpoint|Extension $object
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
