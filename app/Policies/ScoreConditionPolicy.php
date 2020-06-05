<?php

namespace App\Policies;

use App\ScoreCondition;
use App\User;

class ScoreConditionPolicy extends Policy
{
    /**
     * @var ValueSchemePolicy
     */
    protected $valueSchemePolicy;

    /**
     * Create a new controller instance.
     *
     * @param ValueSchemePolicy $valueSchemePolicy
     */
    public function __construct(ValueSchemePolicy $valueSchemePolicy)
    {
        $this->valueSchemePolicy = $valueSchemePolicy;
    }

    /**
     * Determine whether the user can index the model.
     *
     * @param User $user
     * @param ScoreCondition $condition
     * @return bool
     */
    public function index(User $user, ScoreCondition $condition)
    {
        return $this->valueSchemePolicy->view($user, $condition->scheme);
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param User $user
     * @param ScoreCondition $condition
     * @return bool
     */
    public function view(User $user, ScoreCondition $condition)
    {
        return $this->valueSchemePolicy->view($user, $condition->scheme);
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param ScoreCondition $condition
     * @return bool
     */
    public function update(User $user, ScoreCondition $condition)
    {
        return $this->valueSchemePolicy->update($user, $condition->scheme);
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param ScoreCondition $condition
     * @return bool
     */
    public function delete(User $user, ScoreCondition $condition)
    {
        return $this->valueSchemePolicy->update($user, $condition->scheme);
    }
}
