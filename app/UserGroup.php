<?php

namespace App;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserGroup extends Pivot
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
    protected $table = 'user_group';

    /**
     * The name of the foreign key column.
     *
     * @var string
     */
    protected $foreignKey = 'userId';

    /**
     * The name of the "other key" column.
     *
     * @var string
     */
    protected $relatedKey = 'groupId';

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
     * Returns the validation rules for the model.
     *
     * @param string $action
     * @param array $merge
     * @param string $id
     * @return array
     */
    public static function rules($action, $merge = [], $id = null)
    {
        if (!in_array($action, ['update'])) {
            throw new RuntimeException('Unknown action: ' . $action);
        }
        $rules = [
            'id' => 'required|exists:users,id',
        ];
        return array_merge($rules, $merge);
    }
}
