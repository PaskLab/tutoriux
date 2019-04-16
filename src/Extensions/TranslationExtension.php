<?php

namespace App\Extensions;

use App\Entity\Locale;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Doctrine\Common\Persistence\Proxy;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * Library of helper functions
 */
class TranslationExtension extends AbstractExtension
{
    /**
     * @var Registry
     */
    protected $doctrine;

    /**
     * @var ArrayCollection
     */
    protected $locales;

    /**
     * TranslationExtension constructor.
     * @param RegistryInterface $doctrine
     */
    public function __construct(RegistryInterface $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    /**
     * List of available filters
     *
     * @return array
     */
    public function getFilters()
    {
        return [new TwigFilter('transTitle', [$this, 'transTitle'])];
    }

    /**
     * This filter try to generate a string representation of an entity in the current locale.
     * If there is no usable representation available, a fallback mechanism is launch and every
     * active locales are tried until one provides an usable result.
     *
     * @param $entity
     * @return string
     * @throws \ReflectionException
     */
    public function transTitle($entity)
    {
        // Fallback not necessary, entity provides a usable string representation in the current locale.
        if ((string) $entity || !$this->isTranslatable($entity)) {
            return $entity;
        }

        $entityPreviousLocale = $entity->getCurrentLocale();

        if (false == $this->locales) {
            $this->locales = $this->doctrine->getManager()->getRepository(Locale::class)->findBy(
                array('active' => true),
                array('ordering' => 'ASC')
            );
        }

        // fallback to other locales
        foreach ($this->locales as $locale) {

            if ($locale->getCode() === $entityPreviousLocale) {
                continue;
            }

            $entity->setCurrentLocale($locale->getCode());

            if ($fallback = (string) $entity) {
                $entity->setCurrentLocale($entityPreviousLocale);

                return $fallback;
            }
        }

        return '';
    }

    /**
     * Check if the entity is a Translatable entity
     *
     * @param $entity
     * @return bool
     * @throws \ReflectionException
     */
    protected function isTranslatable($entity)
    {
        if (!is_object($entity)) {
            return false;
        }

        // Support Doctrine Proxies
        $class = ($entity instanceof Proxy)
            ? get_parent_class($entity)
            : get_class($entity);

        $em = $this->doctrine->getManager();

        if ($em->getMetadataFactory()->isTransient($class)) {
            return false;
        }

        $reflectionClass = new \ReflectionClass($class);

        $traitNames = $reflectionClass->getTraitNames();

        return in_array('Tutoriux\DoctrineBehaviorsBundle\Model\Translatable\Translatable', $traitNames)
                && $reflectionClass->hasProperty('translations');
    }

    /**
     * Name of this extension
     *
     * @return string
     */
    public function getName()
    {
        return 'translation_extension';
    }
}
