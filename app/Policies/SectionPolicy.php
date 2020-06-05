<?php

namespace App\Policies;

use App\Section;
use App\User;

class SectionPolicy extends Policy
{
    /**
     * Determine whether the user can index the model.
     *
     * @param User $user
     * @param Section $section
     * @return bool
     */
    public function index(User $user, Section $section)
    {
        $checklist = $section->parentEntry->parent;
        return $this->verifyAccessGrant($user, $checklist, 'view');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param User $user
     * @param Section $section
     * @return bool
     */
    public function view(User $user, Section $section)
    {
        $checklist = $section->parentEntry->parent;
        return $this->verifyAccessGrant($user, $checklist, 'view');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param Section $section
     * @return bool
     */
    public function update(User $user, Section $section)
    {
        $checklist = $section->parentEntry->parent;
        return $this->verifyAccessGrant($user, $checklist, 'update');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param Section $section
     * @return bool
     */
    public function delete(User $user, Section $section)
    {
        $checklist = $section->parentEntry->parent;
        return $this->verifyAccessGrant($user, $checklist, 'update');
    }
}
