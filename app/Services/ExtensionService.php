<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

abstract class ExtensionService extends Service
{
    abstract  public function create(Request $request, array $data);
    abstract  public function update(Request $request, array $data, Model $extension);
}
