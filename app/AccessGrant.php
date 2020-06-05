<?php

namespace App;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use RuntimeException;

class AccessGrant extends Model
{
    use SoftDeletes, Uuid;

    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    const DELETED_AT = 'deletedAt';

    const SUBJECT_TYPE_USER = 'user';
    const SUBJECT_TYPE_GROUP = 'group';
    const OBJECT_TYPE_DIRECTORY = 'directory';
    const OBJECT_TYPE_ARCHIVE_DIRECTORY = 'archive';
    const OBJECT_TYPE_CHECKLIST = 'checklist';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'access_grants';

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
        'view',
        'index',
        'update',
        'delete',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'view' => 'boolean',
        'index' => 'boolean',
        'update' => 'boolean',
        'delete' => 'boolean',
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
            'view' => 'required|boolean',
            'index' => 'required|boolean',
            'update' => 'required|boolean',
            'delete' => 'required|boolean',
        ];
        if ($action == 'create') {
            $rules = array_merge($rules, [
                'subjectId' => 'required|string|size:36',
            ]);
        }
        if ($action == 'update') {
            $rules = array_merge($rules, [
                'view' => 'sometimes|' . $rules['view'],
                'index' => 'sometimes|' . $rules['index'],
                'update' => 'sometimes|' . $rules['update'],
                'delete' => 'sometimes|' . $rules['delete'],
            ]);
        }
        return array_merge($rules, $merge);
    }

    /**
     * Get the grant's subject (Group or User).
     */
    public function subject()
    {
        return $this->morphTo('subject', 'subjectType', 'subjectId');
    }

    /**
     * Get the grant's object (Directory or Checklist).
     */
    public function object()
    {
        return $this->morphTo('object', 'objectType', 'objectId');
    }
}
