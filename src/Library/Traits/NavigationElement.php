<?php

namespace App\Library\Traits;

use App\Library\NavigationElementInterface;

/**
 * Trait NavigationElementTrait
 *
 * This trait must be used in class implementing NavigationElementInterface
 *
 * @package App\Library\Traits
 */
trait NavigationElement
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
        $this->elementName = $name;

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
     */
    public function setParentElement(NavigationElementInterface $parent = null): NavigationElementInterface
    {
        $this->parentElement = $parent;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getParentElement(): ?NavigationElementInterface
    {
        return $this->parentElement;
    }

    /**
     * @return bool
     */
    public function hasChildrenElements(): bool
    {
        return !empty($this->getChildrenElements());
    }

    /**
     * @param iterable $children
     * @return NavigationElementInterface
     */
    public function setChildrenElements(iterable $children = []): NavigationElementInterface
    {
        $this->childrenElements = $children;

        return $this;
    }

    /**
     * @return iterable
     */
    public function getChildrenElements(): iterable
    {
        return $this->childrenElements;
    }

    /**
     * @param NavigationElementInterface $child
     * @return NavigationElementInterface
     */
    public function addChildElement(NavigationElementInterface $child): NavigationElementInterface
    {
        $this->childrenElements[] = $child;

        return $this;
    }

    /**
     * @param NavigationElementInterface $child
     * @return NavigationElementInterface
     */
    public function removeChildElement(NavigationElementInterface $child): NavigationElementInterface
    {
        foreach ($this->childrenElements as $key => $value) {
            if ($value == $child) {
                unset($this->childrenElements[$key]);
            }
        }

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