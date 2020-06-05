<?php

namespace App\Services;

use App\PictureExtension;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class PictureExtensionService extends ExtensionService
{
    /**
     * @var PictureExtension
     */
    protected $pictureExtension;

    /**
     * Create a new controller instance.
     *
     * @param PictureExtension $pictureExtension
     */
    public function __construct(PictureExtension $pictureExtension)
    {
        $this->pictureExtension = $pictureExtension;
    }

    /**
     * Create a new picture extension.
     *
     * @param Request $request
     * @param array $data
     * @return PictureExtension
     */
    public function create(Request $request, array $data)
    {
        $data = $this->validateArray($request, $data, PictureExtension::rules('create'));
        return new PictureExtension($data);
    }

    /**
     * Update a picture extension.
     *
     * @param Request $request
     * @param array $data
     * @param Model $extension
     * @return void
     */
    public function update(Request $request, array $data, Model $extension)
    {
        //funktioniert nicht
        //$data = $this->validateArray($request, $data, PictureExtension::rules('update'));
        //funktioniert
        $data = $request->input('data');
        $extension->update($data);
    }
}
