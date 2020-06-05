<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    /**
     * @var string
     */
    public static $USER = 'user';

    /**
     * @var string
     */
    public static $ADMIN = 'admin';

    /**
     * @var string
     */
    public static $SUPERADMIN = 'superadmin';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'roles';

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
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
    ];

    /**
     * Returns the 'user' role.
     *
     * @return Role
     */
    public static function user()
    {
        return Role::find(Role::$USER);
    }

    /**
     * Returns the 'admin' role.
     *
     * @return Role
     */
    public static function admin()
    {
        return Role::find(Role::$ADMIN);
    }

    /**
     * Returns the 'superadmin' role.
     *
     * @return Role
     */
    public static function superAdmin()
    {
        return Role::find(Role::$SUPERADMIN);
    }

    /**
     * Get all users with the role.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function users()
    {
        return $this->hasMany(User::class, 'roleId');
    }
}
