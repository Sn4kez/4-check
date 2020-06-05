<?php

namespace App\Policies;

use App\Checklist;
use App\Checkpoint;
use App\Section;
use App\User;

class CheckpointPolicy extends Policy
{
    /**
     * @var ChecklistPolicy
     */
    protected $checklistPolicy;

    /**
     * @var SectionPolicy
     */
    protected $sectionPolicy;

    /**
     * Create a new controller instance.
     *
     * @param ChecklistPolicy $checklistPolicy
     * @param SectionPolicy $sectionPolicy
     */
    public function __construct(ChecklistPolicy $checklistPolicy, SectionPolicy $sectionPolicy)
    {
        $this->checklistPolicy = $checklistPolicy;
        $this->sectionPolicy = $sectionPolicy;
    }

    /**
     * Determine whether the user can index the model.
     *
     * @param User $user
     * @param Checkpoint $checkpoint
     * @return bool
     */
    public function index(User $user, Checkpoint $checkpoint)
    {
        $parentPolicy = $this->parentPolicy($checkpoint);
        return $parentPolicy->view($user, $checkpoint->parentEntry->parent);
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param User $user
     * @param Checkpoint $checkpoint
     * @return bool
     */
    public function view(User $user, Checkpoint $checkpoint)
    {
        $parentPolicy = $this->parentPolicy($checkpoint);
        return $parentPolicy->view($user, $checkpoint->parentEntry->parent);
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param Checkpoint $checkpoint
     * @return bool
     */
    public function update(User $user, Checkpoint $checkpoint)
    {
        $parentPolicy = $this->parentPolicy($checkpoint);
        return $parentPolicy->update($user, $checkpoint->parentEntry->parent);
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param Checkpoint $checkpoint
     * @return bool
     */
    public function delete(User $user, Checkpoint $checkpoint)
    {
        $parentPolicy = $this->parentPolicy($checkpoint);
        return $parentPolicy->update($user, $checkpoint->parentEntry->parent);
    }

    /**
     * Returns the checkpoint's parent policy.
     *
     * @param Checkpoint $checkpoint
     * @return ChecklistPolicy|SectionPolicy
     */
    protected function parentPolicy($checkpoint)
    {
        $cls = get_class($checkpoint->parentEntry->parent);
        if ($cls === Checklist::class) {
            return $this->checklistPolicy;
        } else if ($cls === Section::class) {
            return $this->sectionPolicy;
        } else {
            throw new \InvalidArgumentException('Unknown model: ' . $cls);
        }
    }
}
