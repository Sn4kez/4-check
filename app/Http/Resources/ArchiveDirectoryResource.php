<?php

namespace App\Http\Resources;
use App\ArchiveDirectory;

/**
 * @property string $id
 * @property string $name
 * @property string $description
 * @property \App\DirectoryEntry $parentEntry
 * @property \App\Company $company
 * @property \Illuminate\Support\Carbon $createdAt
 * @property \Illuminate\Support\Carbon $updatedAt
 * @property \Illuminate\Support\Carbon $deletedAt
 */
class ArchiveDirectoryResource extends Resource
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
            'path' => $this->getPath($this->resource),
            'name' => $this->name,
            'description' => $this->description,
            'icon' => $this->icon,
            'parentEntryId' => is_null($this->parentEntry) ? null : $this->parentEntry->id,
            'companyId' => is_null($this->company) ? null : $this->company->id,
            'createdBy' => $this->createdBy,
            'lastUpdatedBy' => $this->lastUpdatedBy,
            'lastUsedBy' => $this->lastUsedBy,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
            'deletedAt' => $this->deletedAt,
        ];
    }

    /**
     * Returns a representation of the directory's path.
     *
     * @param Directory $directory
     * @return array
     */
    protected function getPath(ArchiveDirectory $directory)
    {
        $path = [];
        if (is_null($directory->parentEntry)) {
            return $path;
        }
        while (true) {
            $directory = $directory->parentEntry->parent;
            if (is_null($directory->parentEntry)) {
                // The root directory is not part of the path.
                break;
            }
            $path[] = [
                'name' => $directory->name,
                'id' => $directory->id,
            ];
        }
        return array_reverse($path);
    }
}
