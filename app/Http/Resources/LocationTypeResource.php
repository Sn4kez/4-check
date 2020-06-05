<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class LocationTypeResource extends Resource
{
    /**
     * @property string $id
     * @property \Illuminate\Support\Carbon $deletedAt
     */

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'deletedAt' => $this->deletedAt
        ];
    }
}
