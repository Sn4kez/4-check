<?php

namespace App;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use RuntimeException;

class Phone extends Model
{
    use SoftDeletes, Uuid;

    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    const DELETED_AT = 'deletedAt';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'phones';

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
        'countryCode',
        'nationalNumber',
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
            'countryCode' => 'required|string|max:8|regex:/^\d+$/',
            'nationalNumber' => 'required|string|max:64|regex:/^\d+$/',
            'type' => 'required|string|exists:phone_types,id',
        ];
        if ($action == 'update') {
            $rules = array_merge($rules, [
                'countryCode' => 'sometimes|' . $rules['countryCode'],
                'nationalNumber' => 'sometimes|' . $rules['nationalNumber'],
                'type' => 'sometimes|' . $rules['type'],
            ]);
        }
        return array_merge($rules, $merge);
    }

    /**
     * Get the phones's user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'userId');
    }

    /**
     * Get the phone's type.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function type()
    {
        return $this->belongsTo(PhoneType::class, 'typeId');
    }
}
