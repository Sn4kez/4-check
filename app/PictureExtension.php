<?php

namespace App;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\Rule;

class PictureExtension extends Model
{
    use SoftDeletes, Uuid;

    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    const DELETED_AT = 'deletedAt';
    const TYPES = ['signature', 'media'];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'picture_extensions';

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
        'image', 'type'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [

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

        $validTypes = self::TYPES;

        $rules = [
            'image' => 'string|nullable',
            'type' => ['required', Rule::in($validTypes)],
        ];

        return array_merge($rules, $merge);
    }

    /**
     * Get the object's parent extension.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function extension()
    {
        return $this->morphOne(
            Extension::class,
            'object',
            'objectType',
            'objectId');
    }
}
