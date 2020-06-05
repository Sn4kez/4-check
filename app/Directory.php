<?php

namespace App;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use RuntimeException;

class Directory extends Model
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
    protected $table = 'directories';

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
        'name',
        'description',
        'icon',
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
            'name' => 'required|string|max:128',
            'description' => 'nullable|string',
            'icon' => 'nullable|string',
            'parentId' => 'required|exists:directories,id',
        ];
        if ($action == 'update') {
            $rules = array_merge($rules, [
                'name' => 'sometimes|' . $rules['name'],
                'icon' => 'sometimes|' . $rules['icon'],
                'parentId' => 'sometimes|' . $rules['parentId'],
            ]);
        }
        return array_merge($rules, $merge);
    }

    /**
     * Get the directory's parent entry.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function parentEntry() {
        return $this->morphOne(
            DirectoryEntry::class,
            'object',
            'objectType',
            'objectId');
    }

    /**
     * Get the directory's entries.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function entries()
    {
        return $this->hasMany(DirectoryEntry::class, 'parentId');
    }

    /**
     * Get the company associated with this directory.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function company()
    {
        return $this->belongsTo(Company::class, 'companyId');
    }

    /**
     * Get all of the directory's access grants.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function grants()
    {
        return $this->morphMany(
            AccessGrant::class,
            'object',
            'objectType',
            'objectId');
    }

    /**
     * Get user that has created the directory.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */

    public function createdByUser()
    {
        return $this->belongsTo(User::class, 'createdBy');
    }

    /**
     * Get user that has last updated the directory.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */

    public function lastUpdatedByUser()
    {
        return $this->belongsTo(User::class, 'lastUpdatedBy');
    }

    /**
     * Get user that has last used the directory.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */

    public function lastUsedByUser()
    {
        return $this->belongsTo(User::class, 'lastUsedBy');
    }

    /**
     * Adds an object as a new directory entry.
     *
     * @param Directory|Checklist $object
     * @return DirectoryEntry
     */
    public function entry($object)
    {
        $entry = new DirectoryEntry();
        $entry->parent()->associate($this);
        $entry->object()->associate($object);
        return $entry;
    }
}
