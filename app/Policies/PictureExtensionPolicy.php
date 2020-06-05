<?php

namespace App\Policies;

class PictureExtensionPolicy extends ExtensionPolicy
{
    // This class is empty, since all logic is already in the generic extension
    // policy. This class exists to avoid introducing ugly exceptions when
    // checking for subscriptions. This is because all extensions inherit the
    // ACL of the checkpoint's they're attached to, but may be subject to
    // different subscription models.
}
