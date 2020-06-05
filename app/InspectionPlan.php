<?php

namespace App;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\Rule;
use RuntimeException;

class InspectionPlan extends Model
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
    protected $table = 'inspection_plans';

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
        'monday',
        'tuesday',
        'wednesday',
        'thursday',
        'friday',
        'saturday',
        'sunday',
        'type',
        'factor',
        'startDate',
        'endDate',
        'startTime',
        'endTime',
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
        $validTypes = ['monthly', 'weekly', 'daily', 'hourly'];

        $rules = [
        	'name' => 'required|string',
	        'monday' => 'boolean',
	        'tuesday' => 'boolean',
	        'wednesday' => 'boolean',
	        'thursday' => 'boolean',
	        'friday' => 'boolean',
	        'saturday' => 'boolean',
	        'sunday' => 'boolean',
	        'type' => ['required', Rule::in($validTypes)],
	        'factor' => 'required|integer',
	        'startDate' => 'nullable|date',
	        'endDate' => 'nullable|date',
	        'startTime' => 'required|string',
	        'endTime' => 'required|string',
	        'checklist' => 'required|exists:checklists,id',
            'user' => 'required|exists:users,id',
            'company' => 'required|exists:companies,id',
        ];

        if ($action == 'update') {
            $rules = array_merge($rules, [
            	'name' => 'sometimes|' . $rules['name'],
		        'monday' => 'sometimes|' . $rules['monday'],
		        'tuesday' => 'sometimes|' . $rules['tuesday'],
		        'wednesday' => 'sometimes|' . $rules['wednesday'],
		        'thursday' => 'sometimes|' . $rules['thursday'],
		        'friday' => 'sometimes|' . $rules['friday'],
		        'saturday' => 'sometimes|' . $rules['saturday'],
		        'sunday' => 'sometimes|' . $rules['sunday'],
		        'type' => ['sometimes', Rule::in($validTypes)],
		        'factor' => 'sometimes|' . $rules['factor'],
		        'startDate' => 'sometimes|' . $rules['startDate'],
		        'endDate' => 'sometimes|' . $rules['endDate'],
		        'startTime' => 'sometimes|' . $rules['startTime'],
		        'endTime' => 'sometimes|' . $rules['endTime'],
                'checklist' => 'sometimes|' . $rules['checklist'],
                'user' => 'sometimes|' . $rules['user'],
                'company' => 'sometimes|' . $rules['company']
            ]);
        }

        return array_merge($rules, $merge);
    }

    /**
     * Get the checklist of the inspection paln.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */

    public function checklist() 
    {
    	return $this->belongsTo(Checklist::class, 'checklistId');
    }

    /**
     * Get the user of the inspection paln.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */

    public function user() 
    {
    	return $this->belongsTo(User::class, 'userId');
    }

    /**
     * Get the company of the inspection paln.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */

    public function company() 
    {
        return $this->belongsTo(Company::class, 'companyId');
    }
}
