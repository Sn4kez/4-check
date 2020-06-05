<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Sector extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'sectors';

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
     * Get all companies active in the sector.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function companies()
    {
        return $this->hasMany(Company::class, 'sectorId');
    }
}
