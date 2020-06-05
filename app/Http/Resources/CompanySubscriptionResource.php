<?php

namespace App\Http\Resources;

use App\CompanySubscription;

/**
 * @property boolean $id
 * @property boolean $viewCompanySubscription
 * @property \Illuminate\Support\Carbon $createdAt
 * @property \Illuminate\Support\Carbon $updatedAt
 * @property \Illuminate\Support\Carbon $deletedAt
 */
class CompanySubscriptionResource extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        $representation = [
            'id' => $this->id,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
            'deletedAt' => $this->deletedAt,
            'viewCompanySubscription' => $this->viewCompanySubscription,
        ];
        foreach (CompanySubscription::PROTECTED_MODELS as $model) {
            $name = substr(strrchr($model, '\\'), 1);
            $representation['index' . $name] = $this->{'index' . $name};
            $representation['view' . $name] = $this->{'view' . $name};
            $representation['create' . $name] = $this->{'create' . $name};
            $representation['update' . $name] = $this->{'update' . $name};
            $representation['delete' . $name] = $this->{'delete' . $name};
        }
        return $representation;
    }
}
