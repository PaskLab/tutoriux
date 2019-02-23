<?php

namespace App\Services;

use Doctrine\Common\Persistence\ObjectRepository;
use App\Entity\Section,
    App\Library\EntityInterface,
    App\Library\TranslatableRepositoryInterface;

/**
 * Class DoctrineInit
 * @package SystemBundle\Lib
 */
class DoctrineInit
{
    /**
     * @var ApplicationCore
     */
    private $applicationCore;

    /**
     * DoctrineInit constructor.
     * @param \App\Services\ApplicationCore $applicationCore
     */
    public function __construct(ApplicationCore $applicationCore)
    {
        $this->applicationCore = $applicationCore;
    }

    /**
     * @return ApplicationCore
     */
    public function getApplicationCore(): ApplicationCore
    {
        return $this->applicationCore;
    }

    /**
     * @param \App\Services\ApplicationCore $applicationCore
     * @return $this
     */
    public function setApplicationCore(ApplicationCore $applicationCore)
    {
        $this->applicationCore = $applicationCore;

        return $this;
    }

    /**
     * @param ObjectRepository $repository
     * @return TranslatableRepositoryInterface
     */
    public function initRepository(ObjectRepository $repository): ObjectRepository
    {
        if ($repository instanceof TranslatableRepositoryInterface) {
            if ($this->applicationCore->isEditLocaleEnabled()) {
                $repository->setEditMode(true);
                $repository->setLocale($this->getApplicationCore()->getEditLocale());
            } else {
                $repository->setEditMode(false);
                $repository->setLocale($this->getApplicationCore()->getLocale());
            }
        }

        return $repository;
    }

    /**
     * @param $entity
     * @return mixed
     */
    public function initEntity($entity)
    {
        if ($entity instanceof EntityInterface) {
            // Set the Edit Locale on translatable entities

            if ($this->getApplicationCore()->isInitialized()
                && in_array('Tutoriux\DoctrineBehaviorsBundle\Model\Translatable\Translatable', class_uses($entity))) {

                /** @var Section $entity */
                if ($this->getApplicationCore()->isEditLocaleEnabled()) {
                    $entity->setCurrentLocale($this->getApplicationCore()->getEditLocale());
                } else {
                    $entity->setCurrentLocale($this->getApplicationCore()->getLocale());
                }
            }

        } elseif (is_array($entity)) {

            $entities = [];

            foreach ($entity as $item) {
                $entities[] = $this->initEntity($item);
            }

            $entity = $entities;
        }

        return $entity;
    }
}