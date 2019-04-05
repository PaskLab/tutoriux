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
trait NavigationElementTrait
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
     * @var array
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
     * @var array
     */
    protected $routeParams = [];

    /**
     * @var string
     */
    protected $elementIcon = '';

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
        return !empty($this->getChildren());
    }

    /**
     * @param array $children
     * @return NavigationElementInterface
     */
    public function setChildrenElements(array $children = []): NavigationElementInterface
    {
        $this->childrenElements = $children;

        return $this;
    }

    /**
     * @return array
     */
    public function getChildrenElements(): array
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
     * @param array $routeParams
     * @return NavigationElementInterface
     */
    public function setRouteParams(array $routeParams): NavigationElementInterface
    {
        $this->routeParams = $routeParams;

        return $this;
    }

    /**
     * @param array $params
     * @return array
     */
    public function getRouteParams(array $params = []): array
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