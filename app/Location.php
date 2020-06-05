<?php

namespace App;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use RuntimeException;

class Location extends Model
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
    protected $table = 'locations';

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
        'name',
        'description',
        'street',
        'streetNumber',
        'city',
        'postalCode',
        'province',
        'parentId'
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
            'description' => 'nullable|sometimes|string',
            'type' => 'required|string|exists:location_types,id',
            /**
             * Always 'sometimes' because it CAN have a parent or NOT, it is not really required.
             * But when, it must be a valid string and location uuid
             */
            'parentId' => 'nullable|sometimes|string|exists:locations,id',
            'state' => 'string|exists:location_states,id',
            'street' => 'string|max:128',
            'streetNumber' => 'string|max:6',
            'city' => 'string|max:128',
            'postalCode' => 'string|max:128',
            'province' => 'string|max:128',
            'country' => 'string|exists:countries,id',
            'company' => 'string|exists:companies,id'
        ];

        if ($action == 'update') {
            $rules = array_merge($rules, [
                'name' => 'sometimes|' . $rules['name'],
                'description' => 'nullable|sometimes|' . $rules['description'],
                'type' => 'sometimes|' . $rules['type'],
                'state' => 'sometimes|' . $rules['state'],
                'street' => 'sometimes|' . $rules['street'],
                'streetNumber' => 'sometimes|' . $rules['streetNumber'],
                'city' => 'sometimes|' . $rules['city'],
                'postalCode' => 'sometimes|' . $rules['postalCode'],
                'province' => 'sometimes|' . $rules['province'],
                'country' => 'sometimes|' . $rules['country'],
                'company' => 'sometimes|' . $rules['company']
            ]);
        }

        return array_merge($rules, $merge);
    }

    /**
     * Get the locations company.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function company()
    {
        return $this->belongsTo(Company::class, 'companyId');
    }

    /**
     * Get the locations type.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function type()
    {
        return $this->belongsTo(LocationType::class, 'typeId');
    }

    /**
     * Get the parent location object if found
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function parent()
    {
        return $this->belongsTo(Location::class, 'parentId');
    }

    /**
     * Get the locations type.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function state()
    {
        return $this->belongsTo(LocationState::class, 'stateId');
    }

    /**
     * Get the locations country.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function country()
    {
        return $this->belongsTo(Country::class, 'countryId');
    }

    /**
     * Get the location's checkpoint extensions.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function extensions()
    {
        return $this->hasMany(LocationExtension::class, 'locationId');
    }
}
