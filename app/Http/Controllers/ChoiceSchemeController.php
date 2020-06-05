<?php

namespace App\Http\Controllers;

use App\ChoiceScheme;
use App\Http\Resources\ChoiceSchemeResource;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use App\Events\Checklist\ChecklistEntryUpdatedEvent;

class ChoiceSchemeController extends Controller
{
    /**
     * @var ChoiceScheme
     */
    protected $choiceScheme;

    /**
     * Create a new controller instance.
     *
     * @param ChoiceScheme $choiceScheme
     */
    public function __construct(ChoiceScheme $choiceScheme)
    {
        $this->choiceScheme = $choiceScheme;
    }

    /**
     * View a choice scheme..
     *
     * @param string $schemeId
     * @return \Illuminate\Http\JsonResponse
     */
    public function view(string $schemeId)
    {
        /* @var ChoiceScheme $scheme */
        $scheme = $this->choiceScheme->findOrFail($schemeId);
        $this->authorize($scheme);
        return ChoiceSchemeResource::make($scheme)
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    /**
     * Update a choice scheme.
     *
     * @param Request $request
     * @param string $schemeId
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, string $schemeId)
    {
        /* @var ChoiceScheme $scheme */
        $scheme = $this->choiceScheme->findOrFail($schemeId);
        $this->authorize($scheme);
        $data = $this->validate($request, ChoiceScheme::rules('update'));
        $scheme->update($data);
        $user = $request->user();
        event(new ChecklistEntryUpdatedEvent($schemeId, $user->id, Carbon::Now()));
        return response('', Response::HTTP_NO_CONTENT);
    }
}
