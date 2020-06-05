<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;

trait Uuid
{
    /**
     * Boot the trait.
     */
    protected static function bootUuid()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        static::creating(function (Model $model) {
            $uuid = \Ramsey\Uuid\Uuid::uuid4();
            $model->{$model->getKeyName()} = $uuid->toString();
        });
    }

    /**
     * Get the key type.
     *
     * @return string
     */
    public function getKeyType()
    {
        return 'string';
    }

    /**
     * Get the value indicating whether the key is incrementing.
     *
     * @return bool
     */
    public function getIncrementing()
    {
        return false;
    }
}
