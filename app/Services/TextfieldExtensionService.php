<?php

namespace App\Services;

use App\Checklist;
use App\Http\Resources\SectionResource;
use App\Section;
use App\TextfieldExtension;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TextfieldExtensionService extends ExtensionService
{
    /**
     * @var TextfieldExtension
     */
    protected $textfieldExtension;

    /**
     * Create a new controller instance.
     *
     * @param TextfieldExtension $textfieldExtension
     */
    public function __construct(TextfieldExtension $textfieldExtension)
    {
        $this->textfieldExtension = $textfieldExtension;
    }

    /**
     * Create a new textfield extension.
     *
     * @param Request $request
     * @param array $data
     * @return TextfieldExtension
     */
    public function create(Request $request, array $data)
    {
        $data = $this->validateArray($request, $data, TextfieldExtension::rules('create'));
        return new TextfieldExtension($data);
    }

    /**
     * Update a textfield extension.
     *
     * @param Request $request
     * @param array $data
     * @param Model $extension
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, array $data, Model $extension)
    {
        $data = $this->validateArray($request, $data, TextfieldExtension::rules('update'));
        $extension->update($data);
    }
}
