<?php

namespace App\Http\Controllers;

use App\Checklist;
use App\Http\Resources\ScoreConditionResource;
use App\Http\Resources\SectionResource;
use App\Score;
use App\ScoreCondition;
use App\ScoringScheme;
use App\Section;
use App\ValueScheme;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ScoreConditionController extends Controller
{
    /**
     * @var ScoreCondition
     */
    protected $scoreCondition;

    /**
     * @var ValueScheme
     */
    protected $valueScheme;

    /**
     * @var Score
     */
    protected $score;

    /**
     * Create a new controller instance.
     *
     * @param ScoreCondition $scoreCondition
     * @param ValueScheme $valueScheme
     * @param Score $score
     */
    public function __construct(ScoreCondition $scoreCondition,
                                ValueScheme $valueScheme, Score $score)
    {
        $this->scoreCondition = $scoreCondition;
        $this->valueScheme = $valueScheme;
        $this->score = $score;
    }

    /**
     * Create a new score condition.
     *
     * @param Request $request
     * @param string $schemeId
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(Request $request, string $schemeId)
    {
        /** @var ValueScheme $scheme */
        $scheme = $this->valueScheme->findOrFail($schemeId);
        $this->authorize('update', $scheme);
        $data = $this->validate($request, ScoreCondition::rules('create'));
        $condition = new ScoreCondition($data);
        $score = $this->score->findOrFail($data['scoreId']);
        $condition->score()->associate($score);
        $scheme->conditions()->save($condition);
        return ScoreConditionResource::make($condition)
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * List a value scheme's score conditions.
     *
     * @param string $schemeId
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(string $schemeId)
    {
        /* @var ValueScheme $scheme */
        $scheme = $this->valueScheme->findOrFail($schemeId);
        $this->authorize('view', $scheme);
        return ScoreConditionResource::collection($scheme->conditions)
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    /**
     * View a score condition.
     *
     * @param string $conditionId
     * @return \Illuminate\Http\JsonResponse
     */
    public function view(string $conditionId)
    {
        /* @var ScoreCondition $condition */
        $condition = $this->scoreCondition->findOrFail($conditionId);
        $this->authorize($condition);
        return ScoreConditionResource::make($condition)
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    /**
     * Update a score condition.
     *
     * @param Request $request
     * @param string $conditionId
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, string $conditionId)
    {
        /* @var ScoreCondition $condition */
        $condition = $this->scoreCondition->findOrFail($conditionId);
        $this->authorize($condition);
        $data = $this->validate($request, ScoreCondition::rules('update'));
        if (array_key_exists('scoreId', $data)) {
            $score = $this->score->findOrFail($data['scoreId']);
            $this->authorize('update', $score);
            $condition->score()->associate($score);
        }
        $condition->update($data);
        return response('', Response::HTTP_NO_CONTENT);
    }

    /**
     * Delete a score condition.
     *
     * @param string $conditionId
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(string $conditionId)
    {
        /* @var ScoreCondition $condition */
        $condition = $this->scoreCondition->findOrFail($conditionId);
        $this->authorize($condition);
        $condition->delete();
        return response('', Response::HTTP_NO_CONTENT);
    }
}
