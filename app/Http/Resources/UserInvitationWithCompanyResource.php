<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

/**
 * @property string $token
 * @property string $company
 * @property string $email
 */

class UserInvitationWithCompanyResource extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'token' => $this->token,
            'email' => $this->email,
            'company' => $this->company->id,
            'companyName' => $this->company->name, 
        ];
    }
}
