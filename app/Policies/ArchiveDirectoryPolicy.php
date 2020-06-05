<?php

namespace App\Policies;

use App\ArchiveDirectory;
use App\User;

class ArchiveDirectoryPolicy extends Policy
{
    /**
     * Determine whether the user can index the model.
     *
     * @param User $user
     * @param Directory $directory
     * @return bool
     */
    public function index(User $user, ArchiveDirectory $directory)
    {
        return $this->verifyAccessGrant($user, $directory, 'index');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param User $user
     * @param Directory $directory
     * @return bool
     */
    public function view(User $user, ArchiveDirectory $directory)
    {
        return $this->verifyAccessGrant($user, $directory, 'view');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param Directory $directory
     * @return bool
     */
    public function update(User $user, ArchiveDirectory $directory)
    {
        return $this->verifyAccessGrant($user, $directory, 'update');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param Directory $directory
     * @return bool
     */
    public function delete(User $user, ArchiveDirectory $directory)
    {
        return $this->verifyAccessGrant($user, $directory, 'delete');
    }
}
