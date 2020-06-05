<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CorporateIdentityLogin extends Model
{
    use SoftDeletes;

    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    const DELETED_AT = 'deletedAt';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'corporate_identity_logins';

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
    protected $fillable = [];

    public $incrementing = false;

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
            'id' => 'required|string|min:3',
            'company' => 'required|string|exists:companies,id'
        ];
        if ($action == 'update') {
            $rules = [
                'id' => 'required|string'
            ];
        }
        return array_merge($rules, $merge);
    }

    public function corporateIdentity()
    {
    	return $this->belongsTo(CorporateIdentity::class, 'corporateId');
    }
}
