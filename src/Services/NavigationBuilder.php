<?php

namespace App\Services;

use App\Entity\Section;
use App\Library\NavigationElementInterface;
use Doctrine\Common\Persistence\Proxy;

/**
 * Class NavigationBuilder
 * @package App\Services
 */
class NavigationBuilder
{
    /**
     * A collection of navigation elements representing multi-level hierarchical data
     * @var array
     */
    protected $elements;

    /**
     * The node to be set as selected in the navigation tree
     * @var NavigationElementInterface
     */
    protected $selectedElement;

    /**
     * NavigationBuilder constructor.
     * @param array $elements
     */
    public function __construct(array $elements = [])
    {
        $this->elements = $elements;
    }

    /**
     * @return array
     */
    public function build(): array
    {
        $this->elements = $this->buildLevel($this->elements);

        return $this->elements;
    }

    /**
     * @param array $elements
     * @param NavigationElementInterface|null $parent
     * @param int $level
     * @return array
     */
    protected function buildLevel(array $elements, NavigationElementInterface $parent = null, int $level = 1)
    {
        /** @var NavigationElementInterface $element */
        foreach ($elements as $key => $element) {

            if (false == $element instanceof NavigationElementInterface) {
                throw new \UnexpectedValueException(get_class($element)
                    . ' need to implement the NavigationElementInterface to be usable in the '
                    . get_class($this)
                );
            }

            if (!$element->getParentElement()) {
                $element->setParentElement($parent);
            }

            $element->setSelectedElement(
                get_class($element) == get_class($this->selectedElement)
                && $element->getId() == $this->selectedElement->getId()
            );

            // This is the currently selected element
            // We set every parents of the currently selected element as selected from the current level back to level 1
            if ($element->isSelectedElement()) {

                $parent = $element->getParentElement();

                while ($parent && ($parent instanceof NavigationElementInterface)) {
                    $parent->setSelectedElement(true);
                    $parent = $parent->getParentElement();
                }
            }

            // This element have some children, we start the same process on the children collection
            if ($element->hasChildrenElements()) {
                $this->buildLevel($element->getChildrenElements(), $element, ($level + 1));
            }

        }

        return $elements;
    }

    /**
     * @param array $elements
     * @return NavigationBuilder
     */
    public function setElements(array $elements): NavigationBuilder
    {
        $this->elements = $elements;

        return $this;
    }

    /**
     * @param array $elements
     * @return NavigationBuilder
     */
    public function addElements(array $elements): NavigationBuilder
    {
        $this->elements = array_merge($this->elements, $elements);

        return $this;
    }

    /**
     * @param NavigationElementInterface $element
     * @return NavigationBuilder
     */
    public function addElement(NavigationElementInterface $element): NavigationBuilder
    {
        $this->elements[] = $element;

        return $this;
    }

    /**
     * @param NavigationElementInterface $selectedElement
     * @return NavigationBuilder
     */
    public function setSelectedElement(NavigationElementInterface $selectedElement): NavigationBuilder
    {
        $this->selectedElement = $selectedElement;

        return $this;
    }

    /**
     * @return NavigationElementInterface
     */
    public function getSelectedElement(): NavigationElementInterface
    {
        return $this->selectedElement;
    }

    /**
     * @return array
     */
    public function getElements(): array
    {
        return $this->elements;
    }
}
