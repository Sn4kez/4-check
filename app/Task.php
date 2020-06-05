<?php

namespace App;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use RuntimeException;

class Task extends Model
{
    use Uuid, SoftDeletes;
    
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    const DELETED_AT = 'deletedAt';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tasks';

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
        'giveNotice',
        'doneAt',
        'assignedAt',
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
            'description' => 'nullable|string',
            'giveNotice' => 'nullable|boolean|nullable',
            'doneAt' => 'nullable|date',
            'assignedAt' => 'nullable|date',
            'type' => 'nullable|string|exists:task_types,id',
            'priority' => 'nullable|string|exists:task_priorities,id',
            'state' => 'nullable|string|exists:task_states,id',
            'issuer' => 'required|string|exists:users,id',
            'assignee' => 'required|string|exists:users,id',
            'location' => 'nullable|string|exists:locations,id',
            'company' => 'required|string|exists:companies,id',
            'source_b64' => 'nullable|sometimes|string',
            'image' => 'nullable|sometimes|nullable|string',
        ];

        if ($action == 'update') {
            $rules = array_merge($rules, [
                'name' => 'sometimes|' . $rules['name'],
                'description' => 'sometimes|' . $rules['description'],
                'giveNotice' => 'sometimes|' . $rules['giveNotice'],
                'doneAt' => 'sometimes|' . $rules['doneAt'],
                'assignedAt' => 'sometimes|' . $rules['assignedAt'],
                'type' => 'sometimes|' . $rules['type'],
                'priority' => 'sometimes|' . $rules['priority'],
                'state' => 'sometimes|' . $rules['state'],
                'issuer' => 'sometimes|' . $rules['issuer'],
                'assignee' => 'sometimes|' . $rules['assignee'],
                'location' => 'sometimes|' . $rules['location'],
                'company' => 'sometimes|' . $rules['company']
            ]);
        }
        return array_merge($rules, $merge);
    }

    /**
     * Get the tasks issuer.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */

    public function issuer()
    {
        return $this->belongsTo(User::class, 'issuerId');
    }

    /**
     * Get the tasks person in charge.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigneeId');
    }

    /**
     * Get the type of the task.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */

    public function type() 
    {
    	return $this->belongsTo(TaskType::class, 'typeId');
    }

    /**
     * Get the state of the task.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */

    public function state()
    {
    	return $this->belongsTo(TaskState::class, 'stateId');
    }

    /**
     * Get the priority of the task.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */

    public function priority()
    {
    	return $this->belongsTo(TaskPriority::class, 'priorityId');	
    }

    //ToDo: relationshops for: checklists, additional information

    public function location()
    {
        return $this->belongsTo(Location::class, 'locationId');
    }

    /**
     * Get the location types company.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function company()
    {
        return $this->belongsTo(Company::class, 'companyId');
    }
}	
