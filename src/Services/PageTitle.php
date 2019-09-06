<?php

namespace App\Services;

use App\Library\NavigationElementInterface;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Class PageTitle
 * @package App\Services
 */
class PageTitle
{
    /**
     * @var ArrayCollection
     */
    private $elements;

    /**
     * PageTitle constructor.
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
     * @return PageTitle
     */
    public function addElement(NavigationElementInterface $element): PageTitle
    {
        $this->elements[] = $element;

        return $this;
    }

    /**
     * @param NavigationElementInterface $element
     * @return PageTitle
     */
    public function removeElement(NavigationElementInterface $element): PageTitle
    {
        $this->elements->removeElement($element);

        return $this;
    }

    /**
     * @return iterable
     */
    public function getElements(): iterable
    {
        $elements = array_reverse($this->elements->toArray(), true);

        return $elements;
    }
}
