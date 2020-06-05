<?php

namespace App;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use RuntimeException;

class ChoiceScheme extends Model
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
    protected $table = 'choice_schemes';

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
        'multiselect',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'multiselect' => 'boolean',
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
            'multiselect' => 'required|boolean',
        ];
        if ($action == 'update') {
            $rules = array_merge($rules, [
                'multiselect' => 'sometimes|' . $rules['multiselect'],
            ]);
        }
        return array_merge($rules, $merge);
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
