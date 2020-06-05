<?php

namespace App\Http\Resources;

/**
 * @property string $id
 * @property string $email
 * @property string $firstName
 * @property string $middleName
 * @property string $lastName
 * @property string $image
 * @property \App\Gender $gender
 * @property \App\Company $company
 * @property \App\Role $role
 * @property \App\Locale $locale
 * @property string $timezone
 * @property string $current_package
 * @property \Illuminate\Support\Carbon $createdAt
 * @property \Illuminate\Support\Carbon $updatedAt
 * @property \Illuminate\Support\Carbon $deletedAt
 */
class UserResource extends Resource
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
            'email' => $this->email,
            'firstName' => $this->firstName,
            'middleName' => $this->middleName,
            'lastName' => $this->lastName,
            'image' => $this->image,
            'gender' => $this->gender->id,
            'companyId' => $this->company->id,
            'role' => $this->role->id,
            'locale' => $this->locale->id,
            'timezone' => $this->timezone,
            'isActive' => $this->isActive,
            'current_package' => $this->current_package,
            'lastLogin' => $this->lastLogin,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
            'deletedAt' => $this->deletedAt,
        ];
    }
}
