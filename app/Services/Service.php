<?php

namespace App\Services;

use Illuminate\Http\Request;
use Laravel\Lumen\Routing\ProvidesConvenienceMethods;

class Service
{
    use ProvidesConvenienceMethods;

    /**
     * Validate the given request with the given rules.
     *
     * @param \Illuminate\Http\Request $request
     * @param array $rules
     * @param array $messages
     * @param array $customAttributes
     * @return array
     */
    public function validate(Request $request, array $rules, array $messages = [], array $customAttributes = [])
    {
        $data = $this->filterRequestData($request->all(), $rules);
        $validator = $this->getValidationFactory()->make($data, $rules, $messages, $customAttributes);
        if ($validator->fails()) {
            $this->throwValidationException($request, $validator);
        }
        return $data;
    }

    /**
     * Validate the given request with the given rules.
     *
     * @param Request $request
     * @param array $data
     * @param array $rules
     * @param array $messages
     * @param array $customAttributes
     * @return array
     */
    public function validateArray(Request $request, array $data, array $rules, array $messages = [], array $customAttributes = [])
    {
        $data = $this->filterRequestData($data, $rules);
        $validator = $this->getValidationFactory()->make($data, $rules, $messages, $customAttributes);
        if ($validator->fails()) {
            $this->throwValidationException($request, $validator);
        }
        return $data;
    }

    /**
     * Filter the data array to only include attributes given by the rules.
     *
     * @param array $data
     * @param array $rules
     * @return array
     */
    public function filterRequestData(array $data, array $rules)
    {
        $all = array_dot($data);
        $rules = collect($rules);
        $keys = $rules->keys();
        $filtered = [];
        $rules->each(function ($rule, $key) use ($all, $keys, &$filtered) {
            // Skip this rule, if it has nested rules
            foreach ($keys as $otherKey) {
                if (strpos($otherKey, "$key.")) {
                    return;
                }
            }
            // Explode pipe-delimited rule sets
            if (is_string($rule)) {
                $rule = explode('|', $rule);
            }
            // If the rule expects an array, match all its children
            if (in_array('array', $rule)) {
                $key .= ".*";
            }
            // Filter all attributes matching the rule's key
            foreach ($all as $otherKey => $element) {
                if (fnmatch($key, $otherKey)) {
                    array_set($filtered, $otherKey, $element);
                }
            }
        });
        return $filtered;
    }
}
