<?php

namespace App\Http\Controllers;

use App\Checklist;
use App\ChecklistEntry;
use App\Http\Resources\ChecklistEntryResource;
use App\Http\Resources\SectionResource;
use App\Section;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SectionController extends Controller
{
    /**
     * @var Section
     */
    protected $section;

    /**
     * @var Checklist
     */
    protected $checklist;

    /**
     * Create a new controller instance.
     *
     * @param Section $section
     * @param Checklist $checklist
     */
    public function __construct(Section $section, Checklist $checklist)
    {
        $this->section = $section;
        $this->checklist = $checklist;
    }

    /**
     * Create a new section.
     *
     * @param Request $request
     * @param string $checklistId
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(Request $request, string $checklistId)
    {
        /** @var Checklist $checklist */
        $checklist = $this->checklist->findOrFail($checklistId);
        $this->authorize('update', $checklist);
        $data = $this->validate($request, Section::rules('create'));
        $section = new Section($data);
        $section->save();
        $checklist->entry($section)->save();
        return SectionResource::make($section)
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * List a section's entries.
     *
     * @param string $sectionId
     * @return \Illuminate\Http\JsonResponse
     */
    public function indexEntries(string $sectionId)
    {
        /* @var Section $section */
        $section = $this->section->findOrFail($sectionId);
        $this->authorize('index', $section);
        return ChecklistEntryResource::collection($section->entries)
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    /**
     * List a checklist's sections.
     *
     * @param string $checklistId
     * @return \Illuminate\Http\JsonResponse
     */
    public function indexSections(string $checklistId)
    {
        /* @var Checklist $checklist */
        $checklist = $this->checklist->findOrFail($checklistId);
        $this->authorize('view', $checklist);
        $sections = $this->section->whereHas('parentEntry', function(Builder $query) use ($checklist) {
            $query->where('parentId', '=', $checklist->id);
        });
        return SectionResource::collection($sections->get())
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    /**
     * View a section.
     *
     * @param string $sectionId
     * @return \Illuminate\Http\JsonResponse
     */
    public function view(string $sectionId)
    {
        /* @var Section $section */
        $section = $this->section->findOrFail($sectionId);
        $this->authorize($section);
        return SectionResource::make($section)
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    /**
     * Update a section.
     *
     * @param Request $request
     * @param string $sectionId
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, string $sectionId)
    {
        /* @var Section $section */
        $section = $this->section->findOrFail($sectionId);
        $this->authorize($section);
        $data = $this->validate($request, Section::rules('update'));
        $section->update($data);
        return response('', Response::HTTP_NO_CONTENT);
    }

    /**
     * Delete a section.
     *
     * @param string $sectionId
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(string $sectionId)
    {
        /* @var Section $section */
        $section = $this->section->findOrFail($sectionId);
        $this->authorize($section);
        $section->parentEntry()->delete();
        $section->entries()->delete();
        $section->delete();
        return response('', Response::HTTP_NO_CONTENT);
    }
}
