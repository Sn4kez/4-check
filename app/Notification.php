<?php

namespace App;

use App\Http\Resources\NotificationPreferencesResource;
use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Model;
use RuntimeException;
use App\NotificationPreferences;

/**
 * @property string sender_id
 * @property string user_id
 * @property string link
 * @property string title
 * @property string message
 */
class Notification extends Model {

    use Uuid;

    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';

    const TITLE_TASK_ASSIGNED = "Aufgabe zugewiesen";
    const TITLE_TASK_COMPLETED = "Aufgabe abgeschlossen";
    const TITLE_TASK_UPDATED = "Aufgabe bearbeitet";
    const TITLE_TASK_DELETED = "Aufgabe gelöscht";

    const TITLE_CHECK_NOTIFICATION = "Frage beantwortet";

    const TITLE_AUDIT_ASSIGNED = "Audit zugewiesen";
    const TITLE_AUDIT_COMPLETED = "Audit abgeschlossen";
    const TITLE_AUDIT_RELEASE_REQUIRED = "Audit benötigt Freigabe";
    const TITLE_AUDIT_OVERDUE = "Audit ist fällig";

    const MESSAGE_TASK_ASSIGNED = "Die Aufgabe %s wurde dir zugewiesen";
    const MESSAGE_TASK_COMPLETED = "Die Aufgabe %s wurde abgeschlossen";
    const MESSAGE_TASK_UPDATED = "Die Aufgabe %s wurde bearbeitet";
    const MESSAGE_TASK_DELETED = "Die Aufgabe %s wurde gelöscht";
    const MESSAGE_CHECK_NOTIFICATION = "Jemand hat eine Frage kritisch beantwortet";

    const PERMISSION_NAME_TASK_ASSIGNED = 'taskAssignedNotification';
    const PERMISSION_NAME_TASK_COMPLETED = 'taskCompletedNotification';
    const PERMISSION_NAME_TASK_DELETED = 'taskDeletedNotification';
    const PERMISSION_NAME_TASK_UPDATED = 'taskUpdatedNotification';

    const PERMISSION_NAME_CHECK_NOTIFICATION = 'checklistCriticalRatingNotification';

    const PERMISSION_NAME_AUDIT_ASSIGNED = 'auditAssignedNotification';
    const PERMISSION_NAME_AUDIT_COMPLETED = 'auditCompletedNotification';
    const PERMISSION_NAME_AUDIT_OVERDUE = 'auditOverdueNotification';
    const PERMISSION_NAME_AUDIT_RELEASE_REQUIRED = 'auditReleaseRequiredNotification';

    /**
     * The name of the table which is associated with this model.
     *
     * @var string
     */
    protected $table = 'notifications';

    /**
     * The name of the primary key for this model.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * The var type of the primary key for this model.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [];

    /**
     * The names of the attribute that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = ['user_id', 'sender_id', 'link', 'title', 'message', 'read', 'pushed', 'readAt'];

    /**
     * Returns all validation rules for this model.
     *
     * @param string $action the name of the action, e.g. 'update' or 'create'
     * @param array $additionalForcedRules an array of forced rules which will be merged at the end of the function to the returned value
     * @return array
     */
    public static function rules($action, $additionalForcedRules = []) {
        if (!in_array($action, ['create', 'update', 'read'])) {
            throw new RuntimeException(sprintf('Unknown action %s', $action));
        }

        $rules = [/**
         * This is just an example right now.
         * I just do not know right now if we need the rules somewhere, because
         * notifications are always sent from system.
         *
         * So 'message' is just an example right now.
         * For more examples take a look in the Location.php, there are
         * much rules defined.
         */
            'user_id' => 'required|string|max:255', 'sender_id' => 'required|string|max:255', 'link' => 'required|string|max:255', 'title' => 'required|string|max:255', 'message' => 'sometimes|string|max:10000', 'read' => 'required|int', 'pushed' => 'required|int', 'readAt' => 'required|date'];

        if ($action === 'update') {
            /**
             * If the action is 'update', then we need to change the rules
             * for updates. First merge, so we can take over all default rules,
             * then overwrite them with the logic of the update rules.
             *
             * The word 'sometimes' is set because if could be that at the time
             * of the POST this  value is not present in the array. But so
             * the model does not need to update the value. The value then
             * will just be checked if it is present in the array.
             */
            $rules = array_merge($rules, ['title' => sprintf('sometimes|%s', $rules['title'])]);
        } elseif ($action === 'read') {
            $rules = ['read' => $rules['read']];
        }

        /**
         * We now merge all our rules we set in the code blocks before.
         * IF someone has given the $additionalForcedRules as parameter, then
         * all rules we defined before - and of course which exists in the array
         * of $additionalForcedRules - will be overwritten then.
         */
        return array_merge($rules, $additionalForcedRules);
    }

    /**
     * Returns the user id the notifications belongs to
     *
     * @return string
     */
    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Adds a new notification
     *
     * @param $senderId
     * @param $userId
     * @param $link
     * @param $title
     * @param $message
     * @param $permissionName
     */
    public static function addNotification($senderId, $userId, $link, $title, $message, $permissionName) {
        if (self::doesUserWantToReceiveNotification($permissionName, $userId)) {
            $notification = new Notification();
            $notification->sender_id = $senderId;
            $notification->user_id = $userId;
            $notification->link = $link;
            $notification->title = $title;
            $notification->message = $message;
            $notification->read = 0;
            $notification->pushed = 0;
            $notification->readAt = '1970-01-01 00:00:00';
            $notification->save();
        }
    }

    /**
     * @param string $permissionName
     * @param $userId
     * @return bool
     */
    public static function doesUserWantToReceiveNotification($permissionName, $userId) {
        $preferences = NotificationPreferences::where('userId', '=', $userId)->first();

        if (!is_null($preferences)) {
            return $preferences->{$permissionName} == 1;
        }

        return false;
    }

}