<?php

namespace App;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use RuntimeException;

class Address extends Model
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
    protected $table = 'addresses';

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
        'line1',
        'line2',
        'city',
        'postalCode',
        'province',
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
            'type' => 'required|string|exists:address_types,id',
            'line1' => 'nullable|string|max:128',
            'line2' => 'nullable|string|max:128',
            'city' => 'nullable|string|max:128',
            'postalCode' => 'nullable|string|max:128',
            'province' => 'nullable|string|max:128',
            'country' => 'required|string|exists:countries,id',
        ];
        if ($action == 'update') {
            $rules = array_merge($rules, [
                'type' => 'sometimes|' . $rules['type'],
                'country' => 'sometimes|' . $rules['country'],
            ]);
        }
        return array_merge($rules, $merge);
    }

    /**
     * Get the address' company.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function company()
    {
        return $this->belongsTo(Company::class, 'companyId');
    }

    /**
     * Get the address's type.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function type()
    {
        return $this->belongsTo(AddressType::class, 'typeId');
    }

    /**
     * Get the address's country.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function country()
    {
        return $this->belongsTo(Country::class, 'countryId');
    }
}
