<?php

namespace App;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use PhpParser\Node\Scalar\MagicConst\Dir;
use RuntimeException;

/**
 * @property string id
 */
class Checklist extends Model {
    use SoftDeletes, Uuid;

    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    const DELETED_AT = 'deletedAt';

    const APPROVER_TYPE_USER = 'user';
    const APPROVER_TYPE_GROUP = 'group';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'checklists';

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
        'numberQuestions',
        'needsApproval'
    ];
    private $paths;

    /**
     * Returns the validation rules for the model.
     *
     * @param string $action
     * @param array $merge
     * @param string $id
     * @return array
     */
    public static function rules($action, $merge = [], $id = null) {
        if (!in_array($action, [
            'create',
            'update'
        ])) {
            throw new RuntimeException('Unknown action: ' . $action);
        }
        $rules = [
            'name' => 'required|string|max:128',
            'description' => 'nullable|string',
            'icon' => 'nullable|string',
            'parentId' => 'sometimes|nullable|exists:directories,id',
            'numberQuestions' => 'integer'
        ];
        if ($action == 'update') {
            $rules = array_merge($rules, [
                'name' => 'sometimes|' . $rules['name'],
                'icon' => 'sometimes|' . $rules['icon'],
                'parentId' => 'sometimes|' . $rules['parentId'],
                'numberQuestions' => 'sometimes|' . $rules['numberQuestions']
            ]);
        }
        return array_merge($rules, $merge);
    }

    /**
     * Get the checklist's parent entry.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function parentEntry() {
        return $this->morphOne(DirectoryEntry::class, 'object', 'objectType', 'objectId');
    }

    /**
     * Get the checklist's parent entry.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function archiveParentEntry() {
        return $this->morphOne(ArchiveEntry::class, 'object', 'objectType', 'objectId');
    }

    /**
     * Get the company associated with this checklist.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function company() {
        return $this->belongsTo(Company::class, 'companyId');
    }

    /**
     * Get the checklist's entries.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function entries() {
        return $this->morphMany(ChecklistEntry::class, 'parent', 'parentType', 'parentId');
    }

    /**
     * Get all of the checklist's scoring schemes.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function scoringSchemes() {
        return $this->morphMany(ScoringScheme::class, 'scope', 'scopeType', 'scopeId');
    }

    /**
     * Get all of the groups's access grants.
     */
    public function grants() {
        return $this->morphMany(AccessGrant::class, 'subject', 'subjectType', 'subjectId');
    }

    /**
     * Get user that has created the directory.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */

    public function createdByUser() {
        return $this->belongsTo(User::class, 'createdBy');
    }

    /**
     * Get user that has last updated the directory.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */

    public function lastUpdatedByUser() {
        return $this->belongsTo(User::class, 'lastUpdatedBy');
    }

    /**
     * Get user that has last used the directory.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */

    public function lastUsedByUser() {
        return $this->belongsTo(User::class, 'lastUsedBy');
    }

    /**
     * Get users that are allowed to approve checklist
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */

    public function approvingByUsers() {
        return $this->belongsToMany(User::class, 'checklists_approving_users', 'checklistId', 'userId');
    }

    /**
     * Adds an object as a new extension entry.
     *
     * @param TextfieldExtension|LocationExtension|ParticipantExtension $object
     * @return Extension
     */
    public function extension($object) {
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
    public function entry($object) {
        $entry = new ChecklistEntry();
        $entry->parent()->associate($this);
        $entry->object()->associate($object);
        return $entry;
    }

    public function getPaths() {
        $this->paths = [];
        $directoryEntry = DirectoryEntry::where('objectId', '=', $this->id)->first();

        if ($directoryEntry !== null) {
            $this->readAllDirectoryEntries($directoryEntry);
        }

        if (count($this->paths) > 0) {
            return array_reverse($this->paths);
        }

        return [];
    }

    private function readAllDirectoryEntries($directoryEntry) {
        $directoryObject = null;

        if (!is_null($directoryEntry)) {
            if ($directoryEntry->objectType === 'directory') {
                /** @var Directory $directoryObject */
                $directoryObject = $directoryEntry->object()->first();
                $this->paths[] = $directoryObject->name;
            }

            $parentId = $directoryEntry->parentId;

            $previousDirectory = DirectoryEntry::where('id', '=', $parentId)->first();

            if(is_null($previousDirectory)) {
                $previousDirectory = DirectoryEntry::where('objectId', '=', $parentId)->first();
            }

            $this->readAllDirectoryEntries($previousDirectory);
        }
    }

    /**
     * Get all of the posts that are assigned this tag.
     */
    public function approvingUsers() {
        return $this->morphedByMany('App\User', 'approver');
    }

    /**
     * Get all of the videos that are assigned this tag.
     */
    public function approvingUserGroups() {
        return $this->morphedByMany('App\Group', 'approver');
    }
}
