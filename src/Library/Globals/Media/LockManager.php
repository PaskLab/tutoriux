<?php

namespace MediaBundle\Lib;

use Doctrine\ORM\EntityManager;

use Symfony\Component\DependencyInjection\ContainerAwareTrait,
    Symfony\Component\DependencyInjection\ContainerInterface;

use MediaBundle\Entity\Lock,
    MediaBundle\Entity\Media;

/**
 * Class LockManager
 * @package MediaBundle\Lib
 */
class LockManager
{
    use ContainerAwareTrait;

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * LockManager constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->setContainer($container);
        $this->em = $this->container->get('doctrine')->getManager();
    }

    /**
     * @param array $mediaIds
     * @return bool
     */
    public function isLocked(array $mediaIds)
    {
        $ids = [];

        foreach ($mediaIds as $value) {
            if (is_int($value)) {
                $ids[] = $value;
            } elseif ($value instanceof Media) {
                $ids[] = $value->getId();
            }
        }

        $count = $this->em->getRepository('MediaBundle:Lock')
            ->locksCount($ids);

        return ($count > 0);
    }

    /**
     *
     *
     * @param Media $media
     * @param $entityClass
     * @param $entityId
     */
    public function addLock(Media $media, $entityClass, $entityId)
    {
        if (!$this->lockExist($media, $entityClass, $entityId)) {
            $lock = new Lock();
            $lock
                ->setMedia($media)
                ->setEntityClass($entityClass)
                ->setEntityId($entityId);

            $this->em->persist($lock);
            $this->em->flush();
            $this->updateMedias([$media]);
        }
    }

    /**
     * @param Media $media
     * @param $entityClass
     * @param $entityId
     */
    public function removeLock(Media $media, $entityClass, $entityId)
    {
        $lock = $this->em->getRepository('MediaBundle:Lock')->findOneBy([
            'media' => $media, 'entityClass' => $entityClass, 'entityId' => $entityId
        ]);

        $this->em->remove($lock);
        $this->em->flush();
        $this->updateMedias([$media]);
    }

    /**
     * @param $entityClass
     * @param $entityId
     */
    public function removeLocksOf($entityClass, $entityId)
    {
        $locks = $this->em->getRepository('MediaBundle:Lock')->findBy([
            'entityClass' => $entityClass, 'entityId' => $entityId
        ]);

        $medias = [];
        /** @var Lock $lock */
        foreach ($locks as $lock) {
            $medias[] = $lock->getMedia();
            $this->em->remove($lock);
        }

        $this->em->flush();
        $this->updateMedias($medias);
    }

    /**
     * Disable locks of a Media
     *
     * @param Media $media
     */
    public function unlock(Media $media)
    {
        $locks = $this->em->getRepository('MediaBundle:Lock')->findBy([
            'media' => $media, 'active' => true
        ]);

        /** @var Lock $lock */
        foreach ($$locks as $lock) {
            $lock->setActive(false);
        }

        $this->em->flush();
        $this->updateMedias([$media]);
    }

    /**
     * Re-enable disabled locks of a Media
     *
     * @param Media $media
     */
    public function relock(Media $media)
    {
        $locks = $this->em->getRepository('MediaBundle:Lock')->findBy([
            'media' => $media, 'active' => true
        ]);

        /** @var Lock $lock */
        foreach ($$locks as $lock) {
            $lock->setActive(true);
        }

        $this->em->flush();
        $this->updateMedias([$media]);
    }

    /**
     * @param Media $media
     * @param $entityClass
     * @param $entityId
     * @return null|object
     */
    public function lockExist(Media $media, $entityClass, $entityId)
    {
        $lock = $this->em->getRepository('MediaBundle:Lock')->findOneBy([
            'media' => $media, 'entityClass' => $entityClass, 'entityId' => $entityId
        ]);

        return ($lock);
    }

    /**
     * Update field locked on Media entity to represent its state
     *
     * @param array $medias
     */
    public function updateMedias(array $medias)
    {
        foreach ($medias as $media) {
            if ($media instanceof Media) {
                $media->setLocked($this->isLocked([$media]));
            }
        }

        $this->em->flush();
    }
}