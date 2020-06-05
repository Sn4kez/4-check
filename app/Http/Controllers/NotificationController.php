<?php

namespace App\Http\Controllers;

use App\Notification;
use App\Http\Resources\NotificationResource;

use http\Exception\InvalidArgumentException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Ramsey\Uuid\Uuid;

class NotificationController extends Controller
{

    private $notification;

    const DEFAULT_LIMIT = 20;

    /**
     * NotificationController constructor.
     * @param Notification $notification the notification we work with
     */
    public function __construct(Notification $notification)
    {
        $this->notification = $notification;
    }

    /**
     * Create a new notification
     *
     * @param Request $request the request which is holding the data
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(Request $request)
    {
        /**
         * Validate posted values against the rules defined in the notification model.
         *
         * If validation fails, an exception will be thrown
         */
        $data = $this->validate($request, Notification::rules('create'));

        /**
         * Create notification
         */
        $newNotification = new Notification($data);
        $newNotification->id = Uuid::uuid4()->toString();
        $newNotification->save();

        return NotificationResource::make($newNotification)
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * Marks the notification as read
     *
     * @param Request $request
     * @param string $notificationId
     * @return Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function markAsRead(Request $request, string $notificationId)
    {
        /** @var \App\User $user */
        $user = $request->user();
        $this->authorize('update', $user);

        $notification = Notification::findOrFail($notificationId);
        $data = $this->validate($request, Notification::rules('read'));


        $notification->update([
            'read' => $data['read']
        ]);

        return $this->getNoContentResponse();
    }

    /**
     * Returns notifications
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        /** @var \App\User $user */
        $user = $request->user();
        $userId = $user->id;

        /**
         * Get all possible request params here.
         */
        $limit = $this->getRequestParam($request, 'limit', 30);

        /**
         * Generates a date at midnight x days before today.
         */
        $conditionValueCreatedAt = date("Y-m-d 00:00:00", strtotime(sprintf('-%s days', $limit), time()));

        $notifications = Notification::all()
            ->sortByDesc("createdAt")
            ->take($limit)
            ->where('createdAt', '>=', $conditionValueCreatedAt)
            ->where('user_id', $userId);

        return NotificationResource::collection($notifications)
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    private function getRequestParam($request, $key, $default = null)
    {
        return array_key_exists($key, $request) ? $request[$key] : $default;
    }

    /**
     * Returns a simple response e.g. for create or update
     *
     * @return Response|\Laravel\Lumen\Http\ResponseFactory
     */
    private function getNoContentResponse()
    {
        return response('', Response::HTTP_NO_CONTENT);
    }


}