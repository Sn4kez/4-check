<?php

namespace App\Http\Resources;

use App\ChoiceScheme;
use App\ScoringScheme;
use App\ValueScheme;

/**
 * @property string $id
 * @property string $prompt
 * @property string $mandatory
 * @property float $factor
 * @property string $index
 * @property ScoringScheme $scoringScheme
 * @property ChoiceScheme|ValueScheme $evaluationScheme
 * @property string $evaluationSchemeType
 * @property \Illuminate\Support\Carbon $createdAt
 * @property \Illuminate\Support\Carbon $updatedAt
 * @property \Illuminate\Support\Carbon $deletedAt
 */
class CheckpointResource extends Resource
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
            'prompt' => $this->prompt,
            'description' => $this->description,
            'mandatory' => $this->mandatory,
            'factor' => $this->factor,
            'index' => $this->index,
            'scoringSchemeId' => $this->scoringScheme->id,
            'evaluationSchemeId' => $this->evaluationScheme->id,
            'evaluationSchemeType' => $this->evaluationSchemeType,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
            'deletedAt' => $this->deletedAt,
        ];
    }
}
