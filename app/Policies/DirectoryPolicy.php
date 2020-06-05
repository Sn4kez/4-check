<?php

namespace App\Policies;

use App\Directory;
use App\DirectoryEntry;
use App\User;

class DirectoryPolicy extends Policy {
    /**
     * Determine whether the user can index the model.
     *
     * @param User $user
     * @param Directory $directory
     * @return bool
     */
    public function index(User $user, Directory $directory) {
        return $this->verifyRootOtherwiseGrant($user, $directory, 'index');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param User $user
     * @param Directory $directory
     * @return bool
     */
    public function view(User $user, Directory $directory) {
        return $this->verifyRootOtherwiseGrant($user, $directory, 'view');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param Directory $directory
     * @return bool
     */
    public function update(User $user, Directory $directory) {
        return $this->verifyAccessGrant($user, $directory, 'update');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param Directory $directory
     * @return bool
     */
    public function delete(User $user, Directory $directory) {
        return $this->verifyAccessGrant($user, $directory, 'delete');
    }

    private function verifyRootOtherwiseGrant(User $user, Directory $directory, $fallbackAction) {
        if (is_null($directory->parentEntryId)) {
            if ($directory->companyId === $user->company->id) {
                return true;
            }
        }

        return $this->verifyAccessGrant($user, $directory, $fallbackAction);
    }
}
