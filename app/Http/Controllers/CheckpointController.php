<?php

namespace App\Http\Controllers;

use App\Checklist;
use App\Checkpoint;
use App\ChoiceScheme;
use App\Http\Resources\ChecklistEntryResource;
use App\Http\Resources\CheckpointResource;
use App\ScoreCondition;
use App\ScoringScheme;
use App\Section;
use App\ValueScheme;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class CheckpointController extends Controller
{
    /**
     * @var Checkpoint
     */
    protected $checkpoint;

    /**
     * @var Checklist
     */
    protected $checklist;

    /**
     * @var Section
     */
    protected $section;

    /**
     * @var ScoringScheme
     */
    protected $scoringScheme;

    /**
     * @var ChoiceScheme
     */
    protected $choiceScheme;

    /**
     * @var ValueScheme
     */
    protected $valueScheme;

    /**
     * @var ScoreCondition
     */
    protected $scoreCondition;

    /**
     * @var array
     */
    protected $parentModels;

    /**
     * Create a new controller instance.
     *
     * @param Checkpoint $checkpoint
     * @param Checklist $checklist
     * @param Section $section
     * @param ScoringScheme $scoringScheme
     * @param ChoiceScheme $choiceScheme
     * @param ValueScheme $valueScheme
     * @param ScoreCondition $scoreCondition
     */
    public function __construct(Checkpoint $checkpoint,
                                Checklist $checklist,
                                Section $section,
                                ScoringScheme $scoringScheme,
                                ChoiceScheme $choiceScheme,
                                ValueScheme $valueScheme,
                                ScoreCondition $scoreCondition)
    {
        $this->checkpoint = $checkpoint;
        $this->checklist = $checklist;
        $this->section = $section;
        $this->scoringScheme = $scoringScheme;
        $this->choiceScheme = $choiceScheme;
        $this->valueScheme = $valueScheme;
        $this->scoreCondition = $scoreCondition;
        $this->parentModels = [
            $this->checklist,
            $this->section,
        ];
    }

    /**
     * Create a new section.
     *
     * @param Request $request
     * @param string $parentId
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(Request $request, string $parentId)
    {
        // Fetch the checkpoint's parent (Checklist or Section).
        /** @var Checklist|Section $parent */
        $parent = $this->findParent($parentId);
        if (is_null($parent)) {
            throw new NotFoundHttpException(
                'Invalid \'parentId\': ' . $parentId . '.');
        }
        $this->authorize('update', $parent);

        // Create the checkpoint.
        $data = $this->validate($request, Checkpoint::rules('create'));
        /** @var Checkpoint $checkpoint */
        $checkpoint = $this->checkpoint->newModelInstance($data);

        // Find and associate the requested scoring scheme.
        /** @var ScoringScheme $scoringScheme */
        $scoringScheme = $this->scoringScheme->findOrFail($data['scoringSchemeId']);
        $this->authorize('update', $scoringScheme);
        $checkpoint->scoringScheme()->associate($scoringScheme);

        // Build and associate the requested evaluation scheme.
        /** @var ChoiceScheme|ValueScheme $evaluationScheme */
        $evaluationScheme = $this->createEvaluationScheme(
            $request, $scoringScheme, $data['evaluationScheme']);
        $checkpoint->evaluationScheme()->associate($evaluationScheme);

        // Create and link the checkpoint.
        $checkpoint->save();
        $parent->entry($checkpoint)->save();

        return CheckpointResource::make($checkpoint)
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * List a checkpoint's entries.
     *
     * @param string $checkpointId
     * @return \Illuminate\Http\JsonResponse
     */
    public function indexEntries(string $checkpointId)
    {
        /* @var Checkpoint $checkpoint */
        $checkpoint = $this->checkpoint->findOrFail($checkpointId);
        $this->authorize('index', $checkpoint);
        return ChecklistEntryResource::collection($checkpoint->entries)
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    /**
     * List a parent's checkpoints.
     *
     * @param string $parentId
     * @return \Illuminate\Http\JsonResponse
     */
    public function indexCheckpoints(string $parentId)
    {
        // Fetch the checkpoint's parent (Checklist or Section).
        /** @var Checklist|Section $parent */
        $parent = $this->findParent($parentId);
        if (is_null($parent)) {
            throw new NotFoundHttpException(
                'Invalid \'parentId\': ' . $parentId . '.');
        }
        $this->authorize('view', $parent);

        // Get all checkpoints that have the requested parent.
        $checkpoints = $this->checkpoint->whereHas('parentEntry', function(Builder $query) use ($parent) {
            $query->where('parentId', '=', $parent->id);
        });

        return CheckpointResource::collection($checkpoints->get())
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    /**
     * View a checkpoint.
     *
     * @param string $checkpointId
     * @return \Illuminate\Http\JsonResponse
     */
    public function view(string $checkpointId)
    {
        /* @var Checkpoint $checkpoint */
        $checkpoint = $this->checkpoint->findOrFail($checkpointId);
        $this->authorize($checkpoint);
        return CheckpointResource::make($checkpoint)
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    /**
     * Update a checkpoint.
     *
     * @param Request $request
     * @param string $checkpointId
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, string $checkpointId)
    {
        /* @var Checkpoint $checkpoint */
        $checkpoint = $this->checkpoint->findOrFail($checkpointId);
        $this->authorize($checkpoint);
        $data = $this->validate($request, Checkpoint::rules('update'));
        if (array_key_exists('scoringSchemeId', $data)) {
            /** @var ScoringScheme $newScoringScheme */
            $newScoringScheme = $this->scoringScheme->findOrFail(
                $data['scoringSchemeId']);
            $this->authorize('update', $newScoringScheme);
            $checkpoint->scoringScheme()->associate($newScoringScheme);
        }
        $checkpoint->update($data);
        return response('', Response::HTTP_NO_CONTENT);
    }

    /**
     * Delete a checkpoint.
     *
     * @param string $checkpointId
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(string $checkpointId)
    {
        /* @var Checkpoint $checkpoint */
        $checkpoint = $this->checkpoint->findOrFail($checkpointId);
        $this->authorize($checkpoint);
        $checkpoint->delete();
        $checkpoint->parentEntry->delete();
        return response('', Response::HTTP_NO_CONTENT);
    }

    /**
     * Creates an evaluation scheme (choice or value).
     *
     * @param Request $request
     * @param ScoringScheme $scoringScheme
     * @param array $data
     * @return ValueScheme|\Illuminate\Database\Eloquent\Model
     */
    private function createEvaluationScheme(Request $request, ScoringScheme $scoringScheme, array $data)
    {
        if ($data['type'] == 'choice') {
            return $this->createChoiceScheme($request, $data['data']);
        } else if ($data['type'] == 'value') {
            return $this->createValueScheme($request, $scoringScheme, $data['data']);
        } else {
            throw new UnprocessableEntityHttpException(
                'Unknown evaluation scheme type: ' . $data['type']);
        }
    }

    /**
     * Creates a new choice scheme.
     *
     * @param Request $request
     * @param array $data
     * @return \Illuminate\Database\Eloquent\Model
     */
    private function createChoiceScheme(Request $request, array $data) {
        $data = $this->validateArray(
            $request, $data, ChoiceScheme::rules('create'));
        $scheme = $this->choiceScheme->newModelInstance($data);
        $scheme->save();
        return $scheme;
    }

    /**
     * Creates a new value scheme.
     *
     * @param Request $request
     * @param ScoringScheme $scoringScheme
     * @param array $data
     * @return ValueScheme
     */
    private function createValueScheme(Request $request, ScoringScheme $scoringScheme, array $data) {
        $data = $this->validateArray(
            $request, $data, ValueScheme::rules('create'));
        /** @var ValueScheme $scheme */
        $scheme = $this->valueScheme->newModelInstance($data);
        $scheme->save();
        $conditions = [];
        foreach ($data['scoreConditions'] as $conditionData) {
            /** @var ScoreCondition $condition */
            $condition = $this->scoreCondition->newModelInstance($conditionData);
            $score = $scoringScheme->scores()->findOrFail($conditionData['scoreId']);
            $condition->score()->associate($score);
        }
        $scheme->conditions()->saveMany($conditions);
        return $scheme;
    }

    /**
     * Finds a parent by id.
     *
     * @param string $parentId
     * @return Checklist|Section
     */
    private function findParent($parentId)
    {
        return $this->findIn($parentId, $this->parentModels);
    }

    /**
     * Finds a model by id.
     *
     * @param string $id
     * @param \Illuminate\Database\Eloquent\Model[] $models
     * @return Checklist|Section
     */
    private function findIn($id, $models)
    {
        $subject = null;
        foreach ($models as $model) {
            $subject = $model->find($id);
            if (!is_null($subject)) {
                break;
            }
        }
        return $subject;
    }
}
