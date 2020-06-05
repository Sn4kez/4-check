<?php

namespace App\Policies;

use App\User;
use App\ValueScheme;

class ValueSchemePolicy extends Policy
{
    /**
     * @var CheckpointPolicy
     */
    protected $checkpointPolicy;

    /**
     * Create a new controller instance.
     *
     * @param CheckpointPolicy $checkpointPolicy
     */
    public function __construct(CheckpointPolicy $checkpointPolicy)
    {
        $this->checkpointPolicy = $checkpointPolicy;
    }

    /**
     * Determine whether the user can index the model.
     *
     * @param User $user
     * @param ValueScheme $scheme
     * @return bool
     */
    public function index(User $user, ValueScheme $scheme)
    {
        return $this->checkpointPolicy->view($user, $scheme->checkpoint);
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param User $user
     * @param ValueScheme $scheme
     * @return bool
     */
    public function view(User $user, ValueScheme $scheme)
    {
        return $this->checkpointPolicy->view($user, $scheme->checkpoint);
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param ValueScheme $scheme
     * @return bool
     */
    public function update(User $user, ValueScheme $scheme)
    {
        return $this->checkpointPolicy->update($user, $scheme->checkpoint);
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param ValueScheme $scheme
     * @return bool
     */
    public function delete(User $user, ValueScheme $scheme)
    {
        return $this->checkpointPolicy->update($user, $scheme->checkpoint);
    }
}
