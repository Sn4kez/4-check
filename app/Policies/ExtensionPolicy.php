<?php

namespace App\Policies;

use App\Checkpoint;
use App\Extension;
use App\Section;
use App\User;
use App\Checklist;
use Illuminate\Database\Eloquent\Model;

class ExtensionPolicy extends Policy
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
     * @var CheckpointPolicy
     */
    protected $checkpointPolicy;

    /**
     * Create a new controller instance.
     *
     * @param ChecklistPolicy $checklistPolicy
     * @param SectionPolicy $sectionPolicy
     * @param CheckpointPolicy $checkpointPolicy
     */
    public function __construct(ChecklistPolicy $checklistPolicy,
                                SectionPolicy $sectionPolicy,
                                CheckpointPolicy $checkpointPolicy)
    {
        $this->checklistPolicy = $checklistPolicy;
        $this->sectionPolicy = $sectionPolicy;
        $this->checkpointPolicy = $checkpointPolicy;
    }

    /**
     * Determine whether the user can index the model.
     *
     * @param User $user
     * @param Model $model
     * @return bool
     */
    public function index(User $user, $model)
    {
        /** @noinspection PhpUndefinedFieldInspection */
        /** @var Extension $extension */
        $extension = $model->extension;
        $parentPolicy = $this->parentPolicy($extension);
        return $parentPolicy->view($user, $extension->parentEntry->parent);
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param User $user
     * @param Model $model
     * @return bool
     */
    public function view(User $user, Model $model)
    {
        /** @noinspection PhpUndefinedFieldInspection */
        /** @var Extension $extension */
        $extension = $model->extension;
        $parentPolicy = $this->parentPolicy($extension);
        return $parentPolicy->view($user, $extension->parentEntry->parent);
    }

    /**
     * Determine whether the user can create the model.
     *
     * @param User $user
     * @param Model $model
     * @return bool
     */
    public function create(User $user, Model $model)
    {
        // If we reached this far, the subscription allows creating this
        // extension. Whether we're allowed to attach it to a checkpoint is
        // subject of another policy check.
        return true;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param Model $model
     * @return bool
     */
    public function update(User $user, Model $model)
    {
        /** @noinspection PhpUndefinedFieldInspection */
        /** @var Extension $extension */
        $extension = $model->extension;
        $parentPolicy = $this->parentPolicy($extension);
        return $parentPolicy->update($user, $extension->parentEntry->parent);
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param Model $model
     * @return bool
     */
    public function delete(User $user, Model $model)
    {
        /** @noinspection PhpUndefinedFieldInspection */
        /** @var Extension $extension */
        $extension = $model->extension;
        $parentPolicy = $this->parentPolicy($extension);
        return $parentPolicy->update($user, $extension->parentEntry->parent);
    }

    /**
     * Returns the checkpoint's parent policy.
     *
     * @param Extension $extension
     * @return ChecklistPolicy|SectionPolicy|CheckpointPolicy
     */
    protected function parentPolicy($extension)
    {
        $cls = get_class($extension->parentEntry->parent);
        if ($cls === Checklist::class) {
            return $this->checklistPolicy;
        } else if ($cls === Section::class) {
            return $this->sectionPolicy;
        } else if ($cls === Checkpoint::class) {
            return $this->checkpointPolicy;
        } else {
            throw new \InvalidArgumentException('Unknown model: ' . $cls);
        }
    }
}
