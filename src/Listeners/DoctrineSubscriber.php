<?php

namespace App\Listeners;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;

use App\Services\DoctrineInit;
use Tutoriux\DoctrineBehaviorsBundle\Model\Translatable\TranslatableInterface;

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
        $entity = $args->getEntity();

        if ($entity instanceof TranslatableInterface) {
            $this->doctrineInit->initEntity($entity);
        }
    }
}
