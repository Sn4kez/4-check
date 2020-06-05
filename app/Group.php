<?php

namespace App;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use RuntimeException;

class Group extends Model
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
    protected $table = 'groups';

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
        'image'
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
            'source_b64' => 'nullable|sometimes|string',
            'image' => 'nullable|sometimes|string'
        ];

        if ($action == 'update') {
            $rules = array_merge($rules, [
                'name' => 'sometimes|' . $rules['name'],
                'source_b64' => 'nullable|sometimes|string',
                'image' => 'nullable|sometimes|string'
            ]);
        }

        return array_merge($rules, $merge);
    }

    /**
     * Get the group's company.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function company()
    {
        return $this->belongsTo(Company::class, 'companyId');
    }

    /**
     * Returns the group's users.
     */
    public function users()
    {
        return $this->belongsToMany(
            User::class,
            'user_group',
            'groupId',
            'userId')
            ->using(UserGroup::class)
            ->withTimestamps();
    }

    /**
     * Get all of the groups's access grants.
     */
    public function grants()
    {
        return $this->morphMany(
            AccessGrant::class,
            'subject',
            'subjectType',
            'subjectId');
    }

    /**
     * Get all of the checklists the group can approve.
     */
    public function approvables()
    {
        return $this->morphToMany('App\Checklist', 'approver');
    }

    /**
     * Get all of the noticing scores for the user.
     */
    public function noticedWhen()
    {
        return $this->morphToMany('App\Score', 'critical');
    }
}
