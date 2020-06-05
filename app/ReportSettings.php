<?php

namespace App;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use RuntimeException;

class ReportSettings extends Model
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
    protected $table = 'report_settings';

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
        'showCompanyName',
        'showCompanyAddress',
        'showUsername',
        'showPageNumbers',
        'showExportDate',
        'showVersion',
        'text',
        'logoPosition'
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
            'showCompanyName' => 'required|boolean',
            'showCompanyAddress' => 'required|boolean',
            'showUsername' => 'required|boolean',
            'showPageNumbers' => 'required|boolean',
            'showExportDate' => 'required|boolean',
            'showVersion' => 'required|boolean',
            'text' => 'nullable|string',
            'logoPosition' => 'in:left,right,center',
        ];
        if ($action == 'update') {
            $rules = array_merge($rules, [
                'showCompanyName' => 'sometimes|' . $rules['showCompanyName'],
                'showCompanyAddress' => 'sometimes|' . $rules['showCompanyAddress'],
                'showUsername' => 'sometimes|' . $rules['showUsername'],
                'showPageNumbers' => 'sometimes|' . $rules['showPageNumbers'],
                'showExportDate' => 'sometimes|' . $rules['showExportDate'],
                'showVersion' => 'sometimes|' . $rules['showVersion'],
                'logoPosition' => 'sometimes|' . $rules['logoPosition'],
            ]);
        }
        return array_merge($rules, $merge);
    }

    /**
     * Get the company, the report settings belong to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function company()
    {
        return $this->belongsTo(Company::class, 'companyId');
    }
}
