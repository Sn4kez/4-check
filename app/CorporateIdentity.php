<?php

namespace App;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use RuntimeException;

class CorporateIdentity extends Model
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
    protected $table = 'corporate_identities';

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
        'brand_primary', 'brand_secondary', 'link_color', 'image'
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
            'company' => 'string|exists:companies,id',
            'brand_primary' => 'nullable|string',
            'brand_secondary' => 'nullable|string',
            'link_color' => 'nullable|string',
            'image' => 'nullable|sometimes|nullable|string',
            'source_b64' => 'sometimes|nullable|string'
        ];
        if ($action == 'update') {
            $rules = array_merge($rules, [
                'company' => 'sometimes|' . $rules['company'],
                'brand_primary' => 'sometimes|' . $rules['brand_primary'],
	            'brand_secondary' => 'sometimes|' . $rules['brand_secondary'],
	            'link_color' => 'sometimes|' . $rules['link_color'],
            ]);
        }
        return array_merge($rules, $merge);
    }

    public function company()
    {
    	return $this->belongsTo(Company::class, 'companyId');
    }
}
