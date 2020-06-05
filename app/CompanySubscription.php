<?php

namespace App;

use App\Policies\PictureExtensionPolicy;
use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use RuntimeException;

class CompanySubscription extends Model
{
    use SoftDeletes, Uuid;

    const PROTECTED_MODELS = [
        User::class,
        Company::class,
        Location::class,
        LocationType::class,
        LocationState::class,
        Task::class,
        TaskState::class,
        TaskType::class,
        TaskPriority::class,
        Directory::class,
        ArchiveDirectory::class,
        Checklist::class,
        Section::class,
        ScoringScheme::class,
        Score::class,
        Checkpoint::class,
        ChoiceScheme::class,
        ValueScheme::class,
        ScoreCondition::class,
        TextfieldExtension::class,
        LocationExtension::class,
        ParticipantExtension::class,
        NotificationPreferences::class,
        CorporateIdentity::class,
        ReportSettings::class,
        PictureExtension::class,
        CorporateIdentityLogin::class,
        UserInvitation::class,
        Audit::class,
        Check::class,
        InspectionPlan::class,
    ];

    const STANDARD = [
        User::class,
        Company::class,
        Directory::class,
        ArchiveDirectory::class,
        Checklist::class,
        Section::class,
        ScoringScheme::class,
        Score::class,
        Checkpoint::class,
        ChoiceScheme::class,
        ValueScheme::class,
        ScoreCondition::class,
        TextfieldExtension::class,
        ParticipantExtension::class,
        NotificationPreferences::class,
        ReportSettings::class,
        PictureExtension::class,
        CorporateIdentityLogin::class,
        CorporateIdentity::class,
        UserInvitation::class,
        Audit::class,
        Check::class,
        InspectionPlan::class,
    ];

    const PREMIUM = self::STANDARD + [
        Location::class,
        LocationType::class,
        LocationState::class,
        LocationExtension::class,
        // TODO: Add statistics here.
    ];

    const DELUXE = self::PROTECTED_MODELS;

    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    const DELETED_AT = 'deletedAt';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'company_subscriptions';

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
     * @return array
     */
    public function getFillable()
    {
        $fillable = [
            'viewCompanySubscription',
        ];
        foreach (self::PROTECTED_MODELS as $model) {
            $name = substr(strrchr($model, '\\'), 1);
            $fillable[] = 'index' . $name;
            $fillable[] = 'view' . $name;
            $fillable[] = 'create' . $name;
            $fillable[] = 'update' . $name;
            $fillable[] = 'delete' . $name;
        }
        return $fillable;
    }

    /**
     * The attributes that should be cast to native types.
     *
     * @return array
     */
    public function getCasts()
    {
        $casts = [
            'viewCompanySubscription' => 'boolean',
        ];
        foreach (self::PROTECTED_MODELS as $model) {
            $name = substr(strrchr($model, '\\'), 1);
            $casts['index' . $name] = 'boolean';
            $casts['view' . $name] = 'boolean';
            $casts['create' . $name] = 'boolean';
            $casts['update' . $name] = 'boolean';
            $casts['delete' . $name] = 'boolean';
        }
        return $casts;
    }

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
            'viewCompanySubscription' => 'required|boolean',
        ];
        foreach (self::PROTECTED_MODELS as $model) {
            $name = substr(strrchr($model, '\\'), 1);
            $rules['index' . $name] = 'required|boolean';
            $rules['view' . $name] = 'required|boolean';
            $rules['create' . $name] = 'required|boolean';
            $rules['update' . $name] = 'required|boolean';
            $rules['delete' . $name] = 'required|boolean';
        }
        if ($action == 'update') {
            $updateRules = [
                'viewCompanySubscription' => 'sometimes|' . $rules['viewCompanySubscription'],
            ];
            foreach (self::PROTECTED_MODELS as $model) {
                $name = substr(strrchr($model, '\\'), 1);
                $updateRules['index' . $name] = 'sometimes|' . $rules['index' . $name];
                $updateRules['view' . $name] = 'sometimes|' . $rules['view' . $name];
                $updateRules['create' . $name] = 'sometimes|' . $rules['create' . $name];
                $updateRules['update' . $name] = 'sometimes|' . $rules['update' . $name];
                $updateRules['delete' . $name] = 'sometimes|' . $rules['delete' . $name];
            }
            $rules = array_merge($rules, $updateRules);
        }
        return array_merge($rules, $merge);
    }

    /**
     * Get the company that owns this subscription.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    /**
     * Set the subscription to a given preset.
     *
     * @param array $preset
     */
    public function setPreset($preset)
    {
        // This will control all subscription entries except 'viewCompanySubscription'.
        foreach (self::PROTECTED_MODELS as $model) {
            $name = substr(strrchr($model, '\\'), 1);
            $this->{'index' . $name} = false;
            $this->{'view' . $name} = false;
            $this->{'create' . $name} = false;
            $this->{'update' . $name} = false;
            $this->{'delete' . $name} = false;
}
        foreach ($preset as $model) {
            $name = substr(strrchr($model, '\\'), 1);
            $this->{'index' . $name} = true;
            $this->{'view' . $name} = true;
            $this->{'create' . $name} = true;
            $this->{'update' . $name} = true;
            $this->{'delete' . $name} = true;
        }
        $this->save();
    }
}
