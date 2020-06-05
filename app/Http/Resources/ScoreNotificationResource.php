<?php

namespace App\Http\Resources;

use App\User;
use App\UserGroup;
use Illuminate\Http\Resources\Json\Resource;

class ScoreNotificationResource extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $object = null;
        if($this->objectType == 'group') {
            $object = UserGroup::find($this->objectId);
        } elseif($this->objectType == 'user') {
            $object = User::find($this->objectId);
        }

        return [
            'id' => $this->id,
            'checklistId' => $this->checklistId,
            'scoreId' => $this->scoreId,
            'objectType' => $this->objectType,
            'objectId' => $this->objectId,
            'object' => $object,
        ];
    }
}
