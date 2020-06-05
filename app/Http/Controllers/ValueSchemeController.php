<?php

namespace App\Http\Controllers;

use App\Http\Resources\ValueSchemeResource;
use App\ValueScheme;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use App\Events\Checklist\ChecklistEntryUpdatedEvent;

class ValueSchemeController extends Controller
{
    /**
     * @var ValueScheme
     */
    protected $valueScheme;

    /**
     * Create a new controller instance.
     *
     * @param ValueScheme $valueScheme
     */
    public function __construct(ValueScheme $valueScheme)
    {
        $this->valueScheme = $valueScheme;
    }

    /**
     * View a value scheme.
     *
     * @param string $schemeId
     * @return \Illuminate\Http\JsonResponse
     */
    public function view(string $schemeId)
    {
        /* @var ValueScheme $scheme */
        $scheme = $this->valueScheme->findOrFail($schemeId);
        $this->authorize($scheme);
        return ValueSchemeResource::make($scheme)
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    /**
     * Update a value scheme.
     *
     * @param Request $request
     * @param string $schemeId
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, string $schemeId)
    {
        /* @var ValueScheme $scheme */
        $scheme = $this->valueScheme->findOrFail($schemeId);
        $this->authorize($scheme);
        $data = $this->validate($request, ValueScheme::rules('update'));
        $scheme->update($data);
        $user = $request->user();
        event(new ChecklistEntryUpdatedEvent($schemeId, $user->id, Carbon::Now()));
        return response('', Response::HTTP_NO_CONTENT);
    }
}
