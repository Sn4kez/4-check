<?php

namespace App;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use RuntimeException;

class Score extends Model
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
    protected $table = 'scores';

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
        'value',
        'color'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'value' => 'float',
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
            'value' => 'required|numeric',
            'color' => 'required|string'
        ];
        if ($action == 'update') {
            $rules = array_merge($rules, [
                'name' => 'sometimes|' . $rules['name'],
                'value' => 'sometimes|' . $rules['value'],
                'color' => 'sometimes|' . $rules['color']
            ]);
        }
        return array_merge($rules, $merge);
    }

    /**
     * Get the score's scoring scheme.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function scoringScheme()
    {
        return $this->belongsTo(ScoringScheme::class, 'scoringSchemeId');
    }

    /**
     * Get the conditions referencing the score.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function conditions()
    {
        return $this->hasMany(ScoreCondition::class, 'scoreId');
    }

    /**
     * Get all of the posts that are assigned this tag.
     */
    public function noticedUsers()
    {
        return $this->morphedByMany('App\User', 'critical');
    }

    /**
     * Get all of the videos that are assigned this tag.
     */
    public function noticedUserGroups()
    {
        return $this->morphedByMany('App\Group', 'critical');
    }
}
