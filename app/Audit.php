<?php

namespace App;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use RuntimeException;

/**
 * @property false|string executionAt
 */
class Audit extends Model
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
    protected $table = 'audits';

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
        'executionDue', 'isArchived', 'userId', 'stateId', 'checklistId', 'executionAt'
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
            'executionDue' => 'required|date',
            'executionAt' => 'nullable|date',
            'company' => 'required|exists:companies,id',
            'user' => 'required|exists:users,id',
            'checklist' => 'required|exists:checklists,id',
            'state' => 'required|exists:audit_states,id',
        ];
        if ($action == 'update') {
            $rules = array_merge($rules, [
                'executionAt' => 'sometimes|' . $rules['executionAt'],
                'executionDue' => 'sometimes|' . $rules['executionDue'],
                'company' => 'sometimes|' . $rules['company'],
                'user' => 'sometimes|' . $rules['user'],
                'checklist' => 'sometimes|' . $rules['checklist'],
                'state' => 'sometimes|' . $rules['state'],
            ]);
        }
        return array_merge($rules, $merge);
    }

    /**
     * Get the company associated with this audit.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function company()
    {
        return $this->belongsTo(Company::class, 'companyId');
    }

    /**
     * Get the user associated with this audit.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'userId');
    }

    /**
     * Get the checklist associated with this audit.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function checklist()
    {
        return $this->belongsTo(Checklist::class, 'checklistId');
    }

    /**
     * Get the state associated with this audit.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function state()
    {
        return $this->belongsTo(AuditState::class, 'stateId');
    }

    public function results() 
    {
        return $this->belongsTo(Check::class, 'auditId');
    }
}
