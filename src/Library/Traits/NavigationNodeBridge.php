<?php

namespace App\Library\Traits;

use App\Library\NavigationElementInterface;
use Tutoriux\DoctrineBehaviorsBundle\Model\Tree\NodeInterface;

/**
 * Trait NavigationNodeBridge
 *
 * This Trait is a bridge to bind the NavigationElement methods to the ones of a Node implementing NodeInterface
 *
 * @package App\Library\Traits
 */
trait NavigationNodeBridge
{
    /**
     * @var string
     */
    protected $elementName = '';

    /**
     * @var bool
     */
    protected $selectedElement = false;

    /**
     * @var NavigationElementInterface
     */
    protected $parentElement = null;

    /**
     * @var iterable
     */
    protected $childrenElements = [];

    /**
     * @var int
     */
    protected $elementLevel;

    /**
     * @var string
     */
    protected $route = '';

    /**
     * @var iterable
     */
    protected $routeParams = [];

    /**
     * @var string
     */
    protected $elementIcon = '';

    /**
     * @throws \Exception
     */
    private function implementationCheck()
    {
        if (!($this instanceof NodeInterface)) {
            throw new \Exception('The class shall implement NodeInterface.');
        }
    }

    /**
     * @return string|null
     */
    public function __toString()
    {
        return $this->getElementName();
    }

    /**
     * @param string $name
     * @return NavigationElementInterface
     */
    public function setElementName(string $name): NavigationElementInterface
    {
        $this->navElementName = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getElementName(): ?string
    {
        return $this->elementName;
    }

    /**
     * @return bool
     */
    public function isSelectedElement(): bool
    {
        return $this->selectedElement;
    }

    /**
     * @param bool $selected
     */
    public function setSelectedElement(bool $selected): NavigationElementInterface
    {
        $this->selectedElement = $selected;

        return $this;
    }

    /**
     * @param NavigationElementInterface|null $parent
     * @return NavigationElementInterface
     * @throws \Exception
     */
    public function setParentElement(NavigationElementInterface $parent = null): NavigationElementInterface
    {
        $this->implementationCheck();
        $this->setParent($parent);

        return $this;
    }

    /**
     * @return NavigationElementInterface|null
     * @throws \Exception
     */
    public function getParentElement(): ?NavigationElementInterface
    {
        $this->implementationCheck();
        return $this->getParent();
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function hasChildrenElements(): bool
    {
        $this->implementationCheck();
        return $this->hasChildren();
    }

    /**
     * @param iterable $children
     * @return NavigationElementInterface
     * @throws \Exception
     */
    public function setChildrenElements(iterable $children = []): NavigationElementInterface
    {
        $this->implementationCheck();
        $this->setChildren($children);

        return $this;
    }

    /**
     * @return iterable
     * @throws \Exception
     */
    public function getChildrenElements(): iterable
    {
        $this->implementationCheck();
        return $this->getChildren();
    }

    /**
     * @param NavigationElementInterface $child
     * @return NavigationElementInterface
     * @throws \Exception
     */
    public function addChildElement(NavigationElementInterface $child): NavigationElementInterface
    {
        $this->implementationCheck();
        $this->addChild($child);

        return $this;
    }

    /**
     * @param NavigationElementInterface $child
     * @return NavigationElementInterface
     * @throws \Exception
     */
    public function removeChildElement(NavigationElementInterface $child): NavigationElementInterface
    {
        $this->implementationCheck();
        $this->removeChild($child);

        return $this;
    }

    /**
     * @param int $level
     * @return NavigationElementInterface
     */
    public function setElementLevel(int $level): NavigationElementInterface
    {
        $this->elementLevel = $level;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getElementLevel(): ?int
    {
        return $this->elementLevel;
    }

    /**
     * @param $route
     * @return $this
     */
    public function setRoute(string $route): NavigationElementInterface
    {
        $this->route = $route;

        return $this;
    }

    /**
     * @return string
     */
    public function getRoute(): ?string
    {
        return $this->route;
    }

    /**
     * @param iterable $routeParams
     * @return NavigationElementInterface
     */
    public function setRouteParams(iterable $routeParams): NavigationElementInterface
    {
        $this->routeParams = $routeParams;

        return $this;
    }

    /**
     * @param iterable $params
     * @return iterable
     */
    public function getRouteParams(iterable $params = []): iterable
    {
        return array_merge($this->routeParams, $params);
    }

    /**
     * @param string|null $icon
     * @return NavigationElementInterface
     */
    public function setElementIcon(string $icon = null): NavigationElementInterface
    {
        $this->elementIcon = $icon;

        return $this;
    }

    /**
     * @return string
     */
    public function getElementIcon(): ?string
    {
        return $this->elementIcon;
    }
}