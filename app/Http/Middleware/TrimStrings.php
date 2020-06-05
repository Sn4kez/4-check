<?php

namespace App\Http\Middleware;

class TrimStrings extends TransformsRequest
{
    /**
     * Transform the given value.
     *
     * @param string $key
     * @param mixed $value
     * @return mixed
     */
    protected function transform($key, $value)
    {
        return is_string($value) ? trim($value) : $value;
    }
}
