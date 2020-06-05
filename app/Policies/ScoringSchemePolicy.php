<?php

namespace App\Policies;

use App\ScoringScheme;
use App\User;

class ScoringSchemePolicy extends Policy {
    /**
     * Determine whether the user can index the model.
     *
     * @param User $user
     * @param ScoringScheme $scheme
     * @return bool
     */
    public function index(User $user, ScoringScheme $scheme) {
        // If the scheme is global, evaluate admin access directly.
        if ($scheme->scopeType == ScoringScheme::SCOPE_TYPE_COMPANY) {
            return $user->company->is($scheme->scope);
        }

        // Otherwise consider ACLs of the scope (checklist).
        return $this->verifyAccessGrant($user, $scheme->scope, 'view');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param User $user
     * @param ScoringScheme $scheme
     * @return bool
     */
    public function view(User $user, ScoringScheme $scheme) {
        // If the scheme is global, evaluate admin access directly.
        if ($scheme->scopeType == ScoringScheme::SCOPE_TYPE_COMPANY) {
            return $user->company->is($scheme->scope);
        }
        // Otherwise consider ACLs of the scope (checklist).
        return $user->company->is($scheme->scope);
        return $this->verifyAccessGrant($user, $scheme->scope, 'view');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param ScoringScheme $scheme
     * @return bool
     */
    public function update(User $user, ScoringScheme $scheme) {
        // If the scheme is global, evaluate admin access directly.
        if ($scheme->scopeType == ScoringScheme::SCOPE_TYPE_COMPANY) {
            if ($user->isAdmin()) {
                return $user->company->is($scheme->scope);
            }
            return false;
        }
        return $user->company->is($scheme->scope);
        // Otherwise consider ACLs of the scope (checklist).
        return $this->verifyAccessGrant($user, $scheme->scope, 'update');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param ScoringScheme $scheme
     * @return bool
     */
    public function delete(User $user, ScoringScheme $scheme) {
        // If the scheme is global, evaluate admin access directly.
        if ($scheme->scopeType == ScoringScheme::SCOPE_TYPE_COMPANY) {
            if ($user->isAdmin()) {
                return $user->company->is($scheme->scope);
            }
            return false;
        }
        // Otherwise consider ACLs of the scope (checklist).
        return $this->verifyAccessGrant($user, $scheme->scope, 'update');
    }
}
