<?php

namespace App\Listeners;

use App\Library\BaseDeletableListener;

/**
 * Class RoleDeletableListener
 * @package App\Listeners
 */
class RoleDeletableListener extends BaseDeletableListener
{
    /**
     * @inheritedDoc
     */
    public function isDeletable($entity)
    {
        if (in_array($entity->getRole(), array('ROLE_DEVELOPER', 'ROLE_BACKEND_ADMIN', 'ROLE_ADMIN'))) {
            $this->addError('You can\'t delete this Role.');
        }

        if (count($entity->getUsers()) > 0) {
            $this->addError('This role has one or more users.');
        }

        return $this->validate();
    }
}
