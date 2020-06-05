<?php

namespace App;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use RuntimeException;

class NotificationPreferences extends Model
{
    use Uuid, SoftDeletes;
    
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';
    const DELETED_AT = 'deletedAt';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'notification_preferences';

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
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'deletedAt',
    ];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Indicates if id should be incremented.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'checklistNeedsActivityNotification', 
        'checklistCompletedNotification',
        'checklistDueNotification',
        'checklistAssignedNotification',
        'checklistCriticalRatingNotification',

        'taskCompletedNotification',
        'taskAssignedNotification',
        'taskUpdatedNotification',
        'taskDeletedNotification',
        'taskOverdueNotification',

        'checklistNeedsActivityMail',
        'checklistCompletedMail',
        'checklistDueMail',
        'checklistAssignedMail',
        'checklistCriticalRatingMail',

        'taskCompletedMail',
        'taskAssignedMail',
        'taskUpdatedMail',
        'taskDeletedMail',
        'taskOverdueMail',

        'auditAssignedNotification',
        'auditCompletedNotification',
        'auditOverdueNotification',
        'auditReleaseRequiredNotification'
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
            'user' => 'required|string|exists:user,id',

            'checklistNeedsActivityNotification' => 'required|boolean',
            'checklistCompletedNotification' => 'required|boolean',
            'checklistDueNotification' => 'required|boolean',
            'checklistAssignedNotification' => 'required|boolean',
            'checklistCriticalRatingNotification' => 'required|boolean',

            'taskCompletedNotification' => 'required|boolean',
            'taskAssignedNotification' => 'required|boolean',
            'taskUpdatedNotification' => 'required|boolean',
            'taskDeletedNotification' => 'required|boolean',
            'taskOverdueNotification' => 'required|boolean',

            'checklistNeedsActivityMail' => 'required|boolean',
            'checklistCompletedMail' => 'required|boolean',
            'checklistDueMail' => 'required|boolean',
            'checklistAssignedMail' => 'required|boolean',
            'checklistCriticalRatingMail' => 'required|boolean',

            'taskCompletedMail' => 'required|boolean',
            'taskAssignedMail' => 'required|boolean',
            'taskUpdatedMail' => 'required|boolean',
            'taskDeletedMail' => 'required|boolean',
            'taskOverdueMail' => 'required|boolean',

            'auditAssignedNotification' => 'required|boolean',
            'auditCompletedNotification' => 'required|boolean',
            'auditOverdueNotification' => 'required|boolean',
            'auditReleaseRequiredNotification' => 'required|boolean',
        ];

        if ($action == 'update') {
            $rules = array_merge($rules, [
                'user' => 'sometimes|' . $rules['user'],

                'checklistNeedsActivityNotification' => 'sometimes|' . $rules['checklistNeedsActivityNotification'],
                'checklistCompletedNotification' => 'sometimes|' . $rules['checklistCompletedNotification'],
                'checklistDueNotification' => 'sometimes|' . $rules['checklistDueNotification'],
                'checklistAssignedNotification' => 'sometimes|' . $rules['checklistAssignedNotification'],
                'checklistCriticalRatingNotification' => 'sometimes|' . $rules['checklistCriticalRatingNotification'],

                'taskCompletedNotification' => 'sometimes|' . $rules['taskCompletedNotification'],
                'taskAssignedNotification' => 'sometimes|' . $rules['taskAssignedNotification'],
                'taskUpdatedNotification' => 'sometimes|' . $rules['taskUpdatedNotification'],
                'taskDeletedNotification' => 'sometimes|' . $rules['taskDeletedNotification'],
                'taskOverdueNotification' => 'sometimes|' . $rules['taskOverdueNotification'],

                'checklistNeedsActivityMail' => 'sometimes|' . $rules['checklistNeedsActivityMail'],
                'checklistCompletedMail' => 'sometimes|' . $rules['checklistCompletedMail'],
                'checklistDueMail' => 'sometimes|' . $rules['checklistDueMail'],
                'checklistAssignedMail' => 'sometimes|' . $rules['checklistAssignedMail'],
                'checklistCriticalRatingMail' => 'sometimes|' . $rules['checklistCriticalRatingMail'],

                'taskCompletedMail' => 'sometimes|' . $rules['taskCompletedMail'],
                'taskAssignedMail' => 'sometimes|' . $rules['taskAssignedMail'],
                'taskUpdatedMail' => 'sometimes|' . $rules['taskUpdatedMail'],
                'taskDeletedMail' => 'sometimes|' . $rules['taskDeletedMail'],
                'taskOverdueMail' => 'sometimes|' . $rules['taskOverdueMail'],

                'auditAssignedNotification' => 'sometimes|' . $rules['auditAssignedNotification'],
                'auditCompletedNotification' => 'sometimes|' . $rules['auditCompletedNotification'],
                'auditOverdueNotification' => 'sometimes|' . $rules['auditOverdueNotification'],
                'auditReleaseRequiredNotification' => 'sometimes|' . $rules['auditReleaseRequiredNotification'],
            ]);
        }

        return array_merge($rules, $merge);
    }

    /**
     * Get the user to the preferences.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */

    public function user()
    {
    	return $this->belongsTo(User::class, 'userId');
    }
}
