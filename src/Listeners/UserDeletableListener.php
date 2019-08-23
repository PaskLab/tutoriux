<?php

namespace App\Listeners;

use App\Library\BaseDeletableListener;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class UserDeletableListener
 * @package App\Listeners
 */
class UserDeletableListener extends BaseDeletableListener
{
    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * Constructor
     *
     * @param TokenStorageInterface $sci
     */
    public function __construct(TokenStorageInterface $sci)
    {
        $this->tokenStorage = $sci;

        parent::__construct();
    }

    /**
     * @return object|string
     */
    protected function getCurrentUser()
    {
        return $this->tokenStorage->getToken()->getUser();
    }

    /**
     * @inheritedDoc
     */
    public function isDeletable($entity)
    {
        if ($this->getCurrentUser()->getId() == $entity->getId()) {
            $this->addError('You can\'t delete yourself.');
        }

        return $this->validate();
    }
}
