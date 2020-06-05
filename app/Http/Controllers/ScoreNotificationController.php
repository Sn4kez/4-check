<?php

namespace App\Http\Controllers;

use App\ScoreNotification;
use App\Score;
use App\Http\Resources\ScoreNotificationResource;
use Ramsey\Uuid\Uuid;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ScoreNotificationController extends Controller
{
    public function create(Request $request, string $scoreId)
    {
    	$data = $this->validate($request, ScoreNotification::rules('create'));

    	$score = Score::findOrFail($scoreId);
        $this->authorize('update', $score);

    	$scoreNotification = new ScoreNotification();
    	$scoreNotification->id = Uuid::uuid4()->toString();
    	$scoreNotification->scoreId = $scoreId;
    	$scoreNotification->checklistId = $request->input('checklistId');
    	$scoreNotification->objectType = $request->input('objectType');
    	$scoreNotification->objectId = $request->input('objectId');
    	$scoreNotification->save();

    	return ScoreNotificationResource::make($scoreNotification)
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function delete(Request $request, string $scoreId)
    {
    	$scoreNotification = ScoreNotification::findOrFail($scoreId);

        $score = Score::findOrFail($scoreNotification->scoreId);

        $this->authorize('update', $score);

        $scoreNotification->delete();

    	return response('', Response::HTTP_NO_CONTENT);
    }

    public function index(Request $request, string $scoreId, string $checklistId)
    {
        $score = Score::findOrFail($scoreId);

        $this->authorize('update', $score);

    	$scoreNotifications = ScoreNotification::where('scoreId', '=', $scoreId)
    		->where('checklistId', '=', $checklistId)
    		->get();

    	return ScoreNotificationResource::collection($scoreNotifications)
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }
}
