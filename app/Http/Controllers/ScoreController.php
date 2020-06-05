<?php

namespace App\Http\Controllers;

use App\Http\Resources\PhoneResource;
use App\Http\Resources\ScoreResource;
use App\Phone;
use App\PhoneType;
use App\Score;
use App\ScoringScheme;
use App\User;
use App\Group;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ScoreController extends Controller
{
    /**
     * @var Score
     */
    protected $score;

    /**
     * @var ScoringScheme
     */
    protected $scoringScheme;

    /**
     * Create a new controller instance.
     *
     * @param Score $score
     * @param ScoringScheme $scoringScheme
     */
    public function __construct(Score $score, ScoringScheme $scoringScheme)
    {
        $this->score = $score;
        $this->scoringScheme = $scoringScheme;
    }

    /**
     * Create a new score.
     *
     * @param Request $request
     * @param string $schemeId
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(Request $request, string $schemeId)
    {
        $scheme = $this->scoringScheme->findOrFail($schemeId);
        $this->authorize('update', $scheme);
        $data = $this->validate($request, Score::rules('create'));
        $score = new Score($data);
        $scheme->scores()->save($score);
        return ScoreResource::make($score)
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * List a scoring scheme's scores.
     *
     * @param string $schemeId
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(string $schemeId)
    {
        /* @var ScoringScheme $scheme */
        $scheme = $this->scoringScheme->findOrFail($schemeId);
        $this->authorize('view', $scheme);
        return ScoreResource::collection($scheme->scores)
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    /**
     * View a score.
     *
     * @param string $scoreId
     * @return \Illuminate\Http\JsonResponse
     */
    public function view(string $scoreId)
    {
        /* @var Score $score */
        $score = $this->score->findOrFail($scoreId);
        $this->authorize($score);
        return ScoreResource::make($score)
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    /**
     * Update a score.
     *
     * @param Request $request
     * @param string $scoreId
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, string $scoreId)
    {
        /* @var Score $score */
        $score = $this->score->findOrFail($scoreId);
        $this->authorize($score);
        $data = $this->validate($request, Score::rules('update'));
        $score->update($data);
        return response('', Response::HTTP_NO_CONTENT);
    }

    /**
     * Delete a score.
     *
     * @param string $scoreId
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(string $scoreId)
    {
        /* @var Score $score */
        $score = $this->score->findOrFail($scoreId);
        $this->authorize($score);
        $score->delete();
        return response('', Response::HTTP_NO_CONTENT);
    }

    /**
     * syncs users to notificate for scores.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function syncNotifications(Request $request)
    {
        if($request->has('data')) {
            $scores = $request->input('data');
            foreach($scores as $item) {
                $score = Score::findOrFail($item['id']);
                $this->authorize('update', $score);
                $noticedUsers = $score->noticedUsers;
                $noticedGroups = $score->noticedUserGroups;

                foreach($item['entries'] as $notifiedObject) {
                    $object = null;

                    if($notifiedObject['objectType'] == 'user') {
                        if(!$noticedUsers->contains('id', $notifiedObject['objectId']))
                        {
                            $object = User::findOrFail($notifiedObject['objectId']);
                            $object->noticedWhen()->attach($score);
                        } else {
                            $noticedUsers->forget($notifiedObject['objectId']);
                        }
                    } else if($notifiedObject['objectType'] == 'group') {
                        if(!$noticedGroups->contains('id', $notifiedObject['objectId']))
                        {
                            $object = UserGroup::findOrFail($notifiedObject['objectId']);
                            $object->noticedWhen()->attach($score);
                        } else {
                            $noticedGroups->forget($notifiedObject['objectId']);
                        }
                    }
                }

                foreach($noticedUsers as $user) {
                    $user->noticedWhen()->detach($score);
                }

                foreach($noticedGroups as $group) {
                    $group->noticedWhen()->detach($score);
                }
            }
        }
    }
}
