<?php

namespace App;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Model;
use RuntimeException;

class UserInvitation extends Model
{
   	use Uuid;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_invitations';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'token';

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
    	'email'
    ];

    public $incrementing = false;
    public $timestamps = false;

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
    	if (!in_array($action, ['create', 'update', 'accept'])) {
            throw new RuntimeException('Unknown action: ' . $action);
        }

        $passwordFormat = config('validation.format.password');

        $rules = [
            'email' => 'required|max:128|email|unique:user_invitations,email',
            'company' => 'required|string',
        ];

        if ($action == 'update') {
            $rules = [
                'email' => 'sometimes|' . $rules['email'],
            ];
        }

        if($action == 'accept') {
            $rules = [
            'email' => 'required|max:128|email|unique:users,email,' . $id,
            'firstName' => 'nullable|string|max:128',
            'middleName' => 'nullable|string|max:128',
            'lastName' => 'nullable|string|max:128',
            'gender' => 'required|string|exists:genders,id',
        ];
        }
        return array_merge($rules, $merge);
    }

    /**
     * Get the tasks issuer.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */

    public function company()
    {
        return $this->belongsTo(Company::class, 'companyId');
    }
}
