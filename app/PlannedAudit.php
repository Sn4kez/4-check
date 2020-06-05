<?php

namespace App;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PlannedAudit extends Model
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
    protected $table = 'planned_audits';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * The primary key type.
     *
     * @var string
     */
    protected $keyType = 'string';

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
        'checklist_id',
        'plan_id',
        'startTime',
        'endTime',
        'date'
    ];

    /**
     * Get the associated inspection plan
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function plan()
    {
        return $this->belongsTo(InspectionPlan::class, 'planId');
    }

    /**
     * Get the associated checklist
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */

    public function checklist()
    {
        return $this->belongsTo(Checklist::class, 'checklistId');
    }
}
