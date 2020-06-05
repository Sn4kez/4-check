<?php

namespace App\Policies;

use App\Score;
use App\User;

class ScorePolicy extends Policy
{
    /**
     * @var Score
     */
    protected $scoringSchemePolicy;

    /**
     * Create a new controller instance.
     *
     * @param ScoringSchemePolicy $scoringSchemePolicy
     */
    public function __construct(ScoringSchemePolicy $scoringSchemePolicy)
    {
        $this->scoringSchemePolicy = $scoringSchemePolicy;
    }

    /**
     * Determine whether the user can index the model.
     *
     * @param User $user
     * @param Score $score
     * @return bool
     */
    public function index(User $user, Score $score)
    {
        return $this->scoringSchemePolicy->view($user, $score->scoringScheme);
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param User $user
     * @param Score $score
     * @return bool
     */
    public function view(User $user, Score $score)
    {
        return $this->scoringSchemePolicy->view($user, $score->scoringScheme);
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param Score $score
     * @return bool
     */
    public function update(User $user, Score $score)
    {
        return $this->scoringSchemePolicy->update($user, $score->scoringScheme);
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param Score $score
     * @return bool
     */
    public function delete(User $user, Score $score)
    {
        return $this->scoringSchemePolicy->update($user, $score->scoringScheme);
    }
}
