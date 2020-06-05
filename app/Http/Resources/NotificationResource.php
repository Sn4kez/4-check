<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

/**
 * @property string $id
 * @property string $user_id
 * @property string $sender_id
 * @property string $link
 * @property string $title
 * @property string $message
 * @property int $read
 * @property int $pushed
 * @property \Illuminate\Support\Carbon $createdAt
 * @property \Illuminate\Support\Carbon $updatedAt
 * @property \Illuminate\Support\Carbon $readAt
 */
class NotificationResource extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'sender_id' => $this->sender_id,
            'link' => $this->link,
            'title' => $this->title,
            'message' => $this->message,
            'read' => $this->read,
            'pushed' => $this->pushed,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
            'readAt' => $this->readAt,
        ];
    }
}
