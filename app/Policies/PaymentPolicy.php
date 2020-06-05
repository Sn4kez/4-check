<?php

namespace App\Policies;

use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PaymentPolicy extends Policy
{
    use HandlesAuthorization;

    public function view() {
        return true;
    }

    public function update(User $user) {
        return $user->isAdmin() || $user->isSuperAdmin();
    }

    public function create(User $user) {
        return $this->update($user);
    }

    public function delete() {
        return false;
    }

    public function cancel() {
        return false;
    }
}
