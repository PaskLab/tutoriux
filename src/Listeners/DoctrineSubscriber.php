<?php

namespace App\Listeners;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;

use App\Library\BaseEntity;
use App\Services\DoctrineInit;
use Doctrine\ORM\Proxy\Proxy;

/**
 * Class PostLoadListener
 * @package App\Listeners
 */
class DoctrineSubscriber implements EventSubscriber
{

    /**
     * @var DoctrineInit
     */
    protected $doctrineInit;

    /**
     * @return array|string[]
     */
    public function getSubscribedEvents()
    {
        return [
            Events::postLoad
        ];
    }

    /**
     * PostLoadListener constructor.
     * @param DoctrineInit $doctrineInit
     */
    public function __construct(DoctrineInit $doctrineInit)
    {
        $this->doctrineInit = $doctrineInit;
    }

    /**
     * Post Load
     *
     * @param LifecycleEventArgs $args Arguments
     */
    public function postLoad(LifecycleEventArgs $args)
    {
        /** @var BaseEntity $entity */
        $entity = $args->getEntity();

        if (is_subclass_of(get_class($entity), BaseEntity::class)) {
            $this->doctrineInit->initEntity($entity);
        }
    }
}
