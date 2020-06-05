<?php

namespace App\Http\Resources;

use App\Extension;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $id
 * @property Model $object
 * @property string $objectType
 * @property \Illuminate\Support\Carbon $createdAt
 * @property \Illuminate\Support\Carbon $updatedAt
 * @property \Illuminate\Support\Carbon $deletedAt
 */
class ExtensionResource extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        /** @var mixed $objectResource */
        $objectResource = Extension::TYPES[$this->objectType]['resource'];
        return [
            'id' => $this->id,
            'type' => $this->objectType,
            'object' => $objectResource::make($this->object),
            'index' => is_null($this->index) ? null : $this->index,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
            'deletedAt' => $this->deletedAt,
        ];
    }
}
