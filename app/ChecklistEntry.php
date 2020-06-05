<?php

namespace App;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use RuntimeException;

class ChecklistEntry extends Model
{
    use SoftDeletes, Uuid;

    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    const DELETED_AT = 'deletedAt';

    const PARENT_TYPE_CHECKLIST = 'checklist';
    const PARENT_TYPE_SECTION = 'section';
    const OBJECT_TYPE_SECTION = 'section';
    const OBJECT_TYPE_CHECKPOINT = 'checkpoint';
    const OBJECT_TYPE_EXTENSION = 'extension';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'checklist_entries';

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
        //
    ];

    /**
     * Get the entry's parent (Checklist or Section).
     */
    public function parent()
    {
        return $this->morphTo('parent', 'parentType', 'parentId');
    }

    /**
     * Get the entry's object (Section, Checkpoint or Extension).
     */
    public function object()
    {
        return $this->morphTo('object', 'objectType', 'objectId');
    }
}
