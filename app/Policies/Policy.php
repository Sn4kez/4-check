<?php

namespace App\Policies;

use App\User;
use Laravel\Lumen\Routing\ProvidesConvenienceMethods;
use ReflectionClass;

class Policy
{
    use ProvidesConvenienceMethods;

    /**
     * Determine whether the user can perform the ability on the model.
     *
     * @param User $user
     * @param $ability
     *
     * @return bool
     * @throws \ReflectionException
     */
    public function before($user, $ability)
    {
        if ($user->isSuperAdmin()) {
            return true;
        }
        if (!$this->verifySubscription($user, $ability)) {
            return false;
        }
        return null;
    }

    /**
     * Verify whether the user's subscription allows the ability on the model.
     *
     * @param $user
     * @param $ability
     * @return mixed
     * @throws \ReflectionException
     */
    public function verifySubscription($user, $ability)
    {
        $subscription = $user->company->companySubscription;
        $reflectionClass = new ReflectionClass($this);
        $model = str_replace('Policy', '', $reflectionClass->getShortName());

        if(is_null($subscription)) {
            return true;
        }

        return $subscription->{$ability . $model};
    }

    /**
     * Verify whether the user was granted the ability on the object.
     *
     * @param \App\User $user
     * @param \App\Directory|\App\Checklist $object
     * @param string $ability
     * @return boolean
     */
    public function verifyAccessGrant($user, $object, $ability)
    {
        // TODO: See how this scales, complexity is horrible obviously.
        $result = false;
        // Test the object tree upwards
        while(!is_null($object)) {
            if ($user->isAdmin()) {
                $result = $user->company->is($object->company);
                if ($result) {
                    break;
                }
            }
            // Look at each grant for the object
            foreach ($object->grants as $grant) {
                /* @var \App\AccessGrant $grant */
                // If the user has a direct grant, we're happy.
                if(count($user->groups) == 0) {
                    if ($grant->subject->is($user)) {
                        $result = $grant->{$ability};
                    }
                    if ($result) {
                        break;
                    }
                } else {
                    // Otherwise check whether the user's in a group with a grant.
                    foreach ($user->groups as $group) {
                        if ($grant->subject->is($group)) {
                            $result = $grant->{$ability};
                        }
                        if ($result) {
                            break;
                        }
                    }
                    if ($result) {
                        break;
                    }
                }
            }
            if ($result) {
                break;
            }
            $parentEntry = $object->parentEntry;
            if (!is_null($parentEntry)) {
                $object = $parentEntry->parent;
            } else {
                $object = null;
            }
        }
        return $result;
    }
}
