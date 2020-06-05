<?php

namespace App;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use RuntimeException;

class ValueScheme extends Model
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
    protected $table = 'value_schemes';

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
        'unit',
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
            'unit' => 'required|string|max:128',
        ];
        if ($action == 'create') {
            $rules = array_merge($rules, [
                'scoreConditions.*.from' => 'nullable|numeric',
                'scoreConditions.*.to' => 'nullable|numeric',
                'scoreConditions.*.scoreId' => 'required|string|exists:scores,id',
            ]);
        }
        if ($action == 'update') {
            $rules = array_merge($rules, [
                'unit' => 'sometimes|' . $rules['unit'],
            ]);
        }
        return array_merge($rules, $merge);
    }

    /**
     * Get the value scheme's conditions.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function conditions()
    {
        return $this->hasMany(ScoreCondition::class, 'valueSchemeId');
    }

    /**
     * Get the scheme's checkpoint.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function checkpoint() {
        return $this->morphOne(
            Checkpoint::class,
            'evaluationScheme',
            'evaluationSchemeType',
            'evaluationSchemeId');
    }
}
