<?php

namespace App\Http\Resources;

/**
 * @property string $text
 * @property boolean $fixed
 * @property \Illuminate\Support\Carbon $createdAt
 * @property \Illuminate\Support\Carbon $updatedAt
 * @property \Illuminate\Support\Carbon $deletedAt
 */
class TextfieldExtensionResource extends Resource
{
    // Note, that extension resources do not provide an ID. The ID of the
    // concrete extension object is private, we only expose the ID of the entry
    // in the extensions table, from which we are able to infer the type as well
    // as the object. See \App\Http\Resources\ExtensionResource.

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'text' => $this->text,
            'fixed' => $this->fixed,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
            'deletedAt' => $this->deletedAt,
        ];
    }
}
