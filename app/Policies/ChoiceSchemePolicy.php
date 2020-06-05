<?php

namespace App\Policies;

use App\User;
use App\ChoiceScheme;

class ChoiceSchemePolicy extends Policy
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
     * @param ChoiceScheme $scheme
     * @return bool
     */
    public function index(User $user, ChoiceScheme $scheme)
    {
        return $this->checkpointPolicy->view($user, $scheme->checkpoint);
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param User $user
     * @param ChoiceScheme $scheme
     * @return bool
     */
    public function view(User $user, ChoiceScheme $scheme)
    {
        return $this->checkpointPolicy->view($user, $scheme->checkpoint);
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param ChoiceScheme $scheme
     * @return bool
     */
    public function update(User $user, ChoiceScheme $scheme)
    {
        return $this->checkpointPolicy->update($user, $scheme->checkpoint);
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param ChoiceScheme $scheme
     * @return bool
     */
    public function delete(User $user, ChoiceScheme $scheme)
    {
        return $this->checkpointPolicy->update($user, $scheme->checkpoint);
    }
}
