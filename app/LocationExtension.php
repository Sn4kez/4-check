<?php

namespace App;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LocationExtension extends Model
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
    protected $table = 'location_extensions';

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
        'fixed',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'fixed' => 'boolean',
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
            'locationId' => 'nullable|string|exists:locations,id',
            'fixed' => 'required|boolean',
        ];
        if ($action == 'update') {
            $rules = array_merge($rules, [
                'fixed' => 'sometimes|' . $rules['fixed'],
                'lcoationId' => 'sometimes|' . $rules['locationId'],
            ]);
        }
        return array_merge($rules, $merge);
    }

    /**
     * Get the object's parent extension.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function extension()
    {
        return $this->morphOne(
            Extension::class,
            'object',
            'objectType',
            'objectId');
    }

    /**
     * Get the extension's location.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function location()
    {
        return $this->belongsTo(Location::class, 'locationId');
    }
}
