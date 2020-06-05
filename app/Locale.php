<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Locale extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'locales';

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
     * Get all users using the locale.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function users()
    {
        return $this->hasMany(User::class, 'localeId');
    }
}
