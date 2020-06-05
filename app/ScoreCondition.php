<?php

namespace App;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use RuntimeException;

class ScoreCondition extends Model
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
    protected $table = 'score_conditions';

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
        'from',
        'to',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'from' => 'float',
        'to' => 'float',
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
            'from' => 'nullable|numeric',
            'to' => 'nullable|numeric',
            'scoreId' => 'required|string|exists:scores,id',
        ];
        if ($action == 'update') {
            $rules = array_merge($rules, [
                'scoreId' => 'sometimes|' . $rules['scoreId'],
            ]);
        }
        return array_merge($rules, $merge);
    }

    /**
     * Get the condition's score.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function score()
    {
        return $this->belongsTo(Score::class, 'scoreId');
    }

    /**
     * Get the condition's value scheme.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function scheme()
    {
        return $this->belongsTo(ValueScheme::class, 'valueSchemeId');
    }
}
