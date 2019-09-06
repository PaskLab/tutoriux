<?php

namespace App\Services;

use App\Library\NavigationElementInterface;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Class Breadcrumbs
 * @package App\Services
 */
class Breadcrumbs
{

    /**
     * @var ArrayCollection
     */
    private $elements;

    /**
     * Breadcrumbs constructor.
     * @param iterable $elements
     * @throws \Exception
     */
    public function __construct(iterable $elements = [])
    {
        $this->elements = new ArrayCollection();

        foreach ($elements as $key => $value) {
            if (!($value instanceof NavigationElementInterface)) {
                throw new \Exception('Value from $elements must implement NavigationElementInterface.');
            }
            $this->elements->set($key, $value);
        }
    }

    /**
     * @param NavigationElementInterface $element
     * @return Breadcrumbs
     */
    public function addElement(NavigationElementInterface $element): Breadcrumbs
    {
        $this->getElements()[] = $element;

        return $this;
    }

    /**
     * @param NavigationElementInterface $element
     * @return Breadcrumbs
     */
    public function removeElement(NavigationElementInterface $element): Breadcrumbs
    {
        $this->getElements()->removeElement($element);

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getElements(): ArrayCollection
    {
        return $this->elements;
    }
}
