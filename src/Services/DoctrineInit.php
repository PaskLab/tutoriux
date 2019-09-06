<?php

namespace App\Services;

use Doctrine\Common\Persistence\ObjectRepository;
use App\Entity\Section;
use Doctrine\ORM\Proxy\Proxy;
use Tutoriux\DoctrineBehaviorsBundle\Model\Translatable\TranslatableInterface;
use Tutoriux\DoctrineBehaviorsBundle\Model\Repository\TranslatableRepositoryInterface;

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
        if ($entity instanceof Proxy) {
            $classUses = class_uses(get_parent_class($entity));
        } else {
            $classUses = class_uses($entity);
        }

        if (is_subclass_of(get_class($entity), TranslatableInterface::class)) {
            // Set the Edit Locale on translatable entities
            if ($this->getApplicationCore()->isReady()
                && in_array('Tutoriux\DoctrineBehaviorsBundle\Model\Translatable\Translatable', $classUses)) {

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