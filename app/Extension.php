<?php

namespace App;

use App\Http\Resources\LocationExtensionResource;
use App\Http\Resources\ParticipantExtensionResource;
use App\Http\Resources\PictureExtensionResource;
use App\Http\Resources\TextfieldExtensionResource;
use App\Services\LocationExtensionService;
use App\Services\ParticipantExtensionService;
use App\Services\TextfieldExtensionService;
use App\Services\PictureExtensionService;
use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\Rule;


class Extension extends Model
{
    use SoftDeletes, Uuid;

    const TYPES = [
        'textfield' => [
            'model' => TextfieldExtension::class,
            'service' => TextfieldExtensionService::class,
            'resource' => TextfieldExtensionResource::class,
        ],
        'location' => [
            'model' => LocationExtension::class,
            'service' => LocationExtensionService::class,
            'resource' => LocationExtensionResource::class,
        ],
        'participant' => [
            'model' => ParticipantExtension::class,
            'service' => ParticipantExtensionService::class,
            'resource' => ParticipantExtensionResource::class,
        ],
        'picture' => [
            'model' => PictureExtension::class,
            'service' => PictureExtensionService::class,
            'resource' => PictureExtensionResource::class
        ]
    ];

    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    const DELETED_AT = 'deletedAt';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'extensions';

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
        'index'
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

        $validTypes = array_keys(self::TYPES);

        $rules = [
            'type' => ['required', Rule::in($validTypes)],
            'index' => 'nullable',
            'data' => 'required|array',
        ];
        return array_merge($rules, $merge);
    }

    /**
     * Get the extension's parent entry.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function parentEntry()
    {
        return $this->morphOne(
            ChecklistEntry::class,
            'object',
            'objectType',
            'objectId');
    }

    /**
     * Get the extension's object (the concrete extension implementation).
     */
    public function object()
    {
        return $this->morphTo('object', 'objectType', 'objectId');
    }
}
