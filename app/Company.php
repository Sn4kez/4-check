<?php

namespace App;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use RuntimeException;
use Laravel\Cashier\Billable;

/**
 * @property bool isActive
 */
class Company extends Model
{
    use SoftDeletes, Uuid, Billable;

    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    const DELETED_AT = 'deletedAt';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'companies';

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
        'name',
        'isActive'
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
            'sector' => 'required|string|exists:sectors,id',
            'isActive' => 'sometimes|boolean',
        ];
        if ($action == 'update') {
            $rules = array_merge($rules, [
                'name' => 'sometimes|' . $rules['name'],
                'sector' => 'sometimes|' . $rules['sector'],
                'isActive' => $rules['isActive'],
            ]);
        }
        return array_merge($rules, $merge);
    }

    /**
     * Get the companies' users.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function users()
    {
        return $this->hasMany(User::class, 'companyId');
    }

    /**
     * Get the companies' groups.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function groups()
    {
        return $this->hasMany(Group::class, 'companyId');
    }

    /**
     * Get the companies' addresses.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function addresses()
    {
        return $this->hasMany(Address::class, 'companyId');
    }

    /**
     * Get the companies' sector.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function sector()
    {
        return $this->belongsTo(Sector::class, 'sectorId');
    }

    /**
     * Get the companies' directories.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function directory()
    {
        return $this->hasOne(Directory::class, 'companyId');
    }

    /**
     * Get the companies' directories.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function archive()
    {
        return $this->hasOne(ArchiveDirectory::class, 'companyId');
    }

    /**
     * Get the companies' checklists.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function checklists()
    {
        return $this->hasMany(Checklist::class, 'companyId');
    }

    /**
     * Get all of the company's scoring schemes.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function scoringSchemes()
    {
        return $this->morphMany(
            ScoringScheme::class,
            'scope',
            'scopeType',
            'scopeId');
    }

    /**
     * Get the companies' report settings.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function reportSettings()
    {
        return $this->hasOne(ReportSettings::class, 'companyId');
    }

    /**
     * Get the companies' subscription.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function companySubscription()
    {
        return $this->hasOne(CompanySubscription::class, 'companyId');
    }
}
