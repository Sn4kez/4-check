<?php

namespace App\Http\Resources;
use App\ScoreNotification;
use App\Http\Resources\ScoreNotificationResource;

/**
 * @property string $id
 * @property string $name
 * @property float $value
 * @property \App\ScoringScheme $scoringScheme
 * @property \Illuminate\Support\Carbon $createdAt
 * @property \Illuminate\Support\Carbon $updatedAt
 * @property \Illuminate\Support\Carbon $deletedAt
 */
class ScoreResource extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        $scoreNotifications = ScoreNotification::where('scoreId', '=', $this->id)->get();

        return [
            'id' => $this->id,
            'name' => $this->name,
            'value' => $this->value,
            'color' => $this->color,
            'schemeId' => $this->scoringScheme->id,
            'noticed' => ScoreNotificationResource::collection($scoreNotifications),
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
            'deletedAt' => $this->deletedAt,
        ];
    }
}
