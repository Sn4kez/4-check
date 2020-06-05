<?php

namespace App;

use App\Traits\Uuid;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\Rule;
use Laravel\Lumen\Auth\Authorizable;
use Laravel\Passport\HasApiTokens;
use RuntimeException;

/**
 * @property mixed id
 * @property bool isActive
 * @property Company company
 * @property false|string lastLogin
 */
class User extends Model implements AuthenticatableContract, AuthorizableContract {
    use Authenticatable, Authorizable, HasApiTokens, SoftDeletes, Uuid;

    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    const DELETED_AT = 'deletedAt';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'users';

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
        'email',
        'password',
        'firstName',
        'middleName',
        'lastName',
        'timezone',
        'image',
        'isActive',
        'lastLogin'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
    ];

    /**
     * Returns the validation rules for the model.
     *
     * @param string $action
     * @param array $merge
     * @param string $id
     * @return array
     */
    public static function rules($action, $merge = [], $id = null) {
        if (!in_array($action, ['create', 'update', 'resetPassword', 'resetPasswordToken', 'verifyEmail'])) {
            throw new RuntimeException('Unknown action: ' . $action);
        }
        $passwordFormat = config('validation.format.password');
        $rules = [
            'email' => 'required|max:128|email|unique:users,email,' . $id,
            'firstName' => 'nullable|string|max:128',
            'middleName' => 'nullable|string|max:128',
            'lastName' => 'nullable|string|max:128',
            'gender' => 'required|string|exists:genders,id',
            'locale' => 'string|exists:locales,id',
            'timezone' => 'timezone',
            'image' => 'nullable|sometimes|string',
            'source_b64' => 'nullable|sometimes|string',
            'isActive' => 'sometimes|boolean',
        ];
        if ($action == 'create') {
            $rules = array_merge($rules, [
                'password' => 'required|min:6|regex:' . $passwordFormat,
                'company.name' => 'required|string|max:128',
                'company.sector' => 'required|string|exists:sectors,id',
                'phone.countryCode' => 'required|string|max:8|regex:/^\d+$/',
                'phone.nationalNumber' => 'required|string|max:64|regex:/^\d+$/',
            ]);
        }
        if ($action == 'update') {
            $rules = array_merge($rules, [
                'email' => 'sometimes|' . $rules['email'],
                'newPassword' => 'min:6|regex:' . $passwordFormat,
                'currentPassword' => 'required_with:newPassword|min:6|regex:' . $passwordFormat,
                'gender' => 'sometimes|' . $rules['gender'],
                'role' => ['string', Rule::in([Role::$USER, Role::$ADMIN, Role::$SUPERADMIN])],
                'isActive' => $rules['isActive'],
            ]);
        }
        if ($action == 'resetPasswordToken') {
            $rules = [
                'email' => 'required|email|exists:users,email',
            ];
        }
        if ($action == 'resetPassword') {
            $rules = [
                'password' => 'required|min:6|regex:' . $passwordFormat,
                'token' => 'required|string|exists:users,token',
            ];
        }

        if ($action == 'verifyEmail') {
            $rules = [
                'token' => 'required|string|exists:users,token',
            ];
        }
        return array_merge($rules, $merge);
    }

    /**
     * Get the user's gender.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function gender() {
        return $this->belongsTo(Gender::class, 'genderId');
    }

    /**
     * Get the user's phones.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function phones() {
        return $this->hasMany(Phone::class, 'userId');
    }

    /**
     * Get the user's role.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function role() {
        return $this->belongsTo(Role::class, 'roleId');
    }

    /**
     * Returns true, if the user is an administrator.
     */
    public function isAdmin() {
        return $this->role->is(Role::admin());
    }

    /**
     * Returns true, if the user is a super-administrator.
     */
    public function isSuperAdmin() {
        return $this->role->is(Role::superAdmin());
    }

    /**
     * Get the user's locale.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function locale() {
        return $this->belongsTo(Locale::class, 'localeId');
    }

    /**
     * Get the user's company.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function company() {
        return $this->belongsTo(Company::class, 'companyId');
    }

    /**
     * Returns the user's groups.
     */
    public function groups() {
        return $this->belongsToMany(Group::class, 'user_group', 'userId', 'groupId')->using(UserGroup::class)->withTimestamps();
    }

    /**
     * Get users that are allowed to approve checklist
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */

    public function approvableChecklists() {
        return $this->belongsTo(Checklist::class, 'checklists_approving_users', 'userId', 'checklistId');
    }

    /**
     * Get all of the user's access grants.
     */
    public function grants() {
        return $this->morphMany(AccessGrant::class, 'subject', 'subjectType', 'subjectId');
    }

    /**
     * Get the user's checkpoint extensions.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function extensions() {
        return $this->hasMany(ParticipantExtension::class, 'userId');
    }

    /**
     * Get all of the checklists the user can approve.
     */
    public function approvables() {
        return $this->morphToMany('App\Checklist', 'approver');
    }

    /**
     * Get all of the noticing scores for the user.
     */
    public function noticedWhen() {
        return $this->morphToMany('App\Score', 'critical');
    }

    /**
     * @return string
     */
    public function getName() {
        return trim(sprintf("%s %s", $this->firstName, $this->lastName));
    }
}
