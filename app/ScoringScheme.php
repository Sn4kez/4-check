<?php

namespace App;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use RuntimeException;

/**
 * @property mixed id
 * @property mixed name
 */
class ScoringScheme extends Model
{
    use SoftDeletes, Uuid;

    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    const DELETED_AT = 'deletedAt';

    const SCOPE_TYPE_COMPANY = 'company';
    const SCOPE_TYPE_CHECKLIST= 'checklist';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'scoring_schemes';

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
        'isListable'
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
        ];
        if ($action == 'update') {
            $rules = array_merge($rules, [
                'name' => 'sometimes|' . $rules['name'],
            ]);
        }
        return array_merge($rules, $merge);
    }

    /**
     * Get the scoring schemes's scope.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function scope()
    {
        return $this->morphTo('scope', 'scopeType', 'scopeId');
    }

    /**
     * Get the scoring scheme's scores.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function scores()
    {
        return $this->hasMany(Score::class, 'scoringSchemeId');
    }

    /**
     * Get the checkpoints referencing the scoring scheme.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function checkpoints()
    {
        return $this->hasMany(Checkpoint::class, 'scoringSchemeId');
    }
}
