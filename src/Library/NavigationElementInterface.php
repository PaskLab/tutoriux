<?php

namespace App\Library;

/**
 * Interface NavigationElementInterface
 * @package App\Library
 */
interface NavigationElementInterface
{
    /**
     * @return string
     */
    public function __toString();

    /**
     * @return mixed
     */
    public function getId();

    /**
     * @param string $name
     * @return NavigationElementInterface
     */
    public function setElementName(string $name): NavigationElementInterface;

    /**
     * @return string|null
     */
    public function getElementName(): ?string;

    /**
     * @return bool
     */
    public function isSelectedElement(): bool;

    /**
     * @param bool $selected
     * @return NavigationElementInterface
     */
    public function setSelectedElement(bool $selected): NavigationElementInterface;

    /**
     * @param NavigationElementInterface|null $parent
     * @return NavigationElementInterface|null
     */
    public function setParentElement(NavigationElementInterface $parent = null): NavigationElementInterface;

    /**
     * @return NavigationElementInterface|null
     */
    public function getParentElement(): ?NavigationElementInterface;

    /**
     * @return bool
     */
    public function hasChildrenElements(): bool;

    /**
     * @param iterable $children
     * @return NavigationElementInterface
     */
    public function setChildrenElements(iterable $children = []): NavigationElementInterface;

    /**
     * @return iterable
     */
    public function getChildrenElements(): iterable;

    /**
     * @param NavigationElementInterface $child
     * @return NavigationElementInterface
     */
    public function addChildElement(NavigationElementInterface $child): NavigationElementInterface;

    /**
     * @param NavigationElementInterface $child
     * @return NavigationElementInterface
     */
    public function removeChildElement(NavigationElementInterface $child): NavigationElementInterface;

    /**
     * @param int $level
     * @return NavigationElementInterface
     */
    public function setElementLevel(int $level): NavigationElementInterface;

    /**
     * @return int|null
     */
    public function getElementLevel(): ?int;

    /**
     * @param string $route
     * @return NavigationElementInterface
     */
    public function setRoute(string $route): NavigationElementInterface;

    /**
     * @return string
     */
    public function getRoute(): ?string;

    /**
     * @param iterable $routeParams
     * @return NavigationElementInterface
     */
    public function setRouteParams(iterable $routeParams): NavigationElementInterface;

    /**
     * @param iterable $params
     * @return iterable
     */
    public function getRouteParams(iterable $params = []): iterable;

    /**
     * @param string|null $icon
     * @return NavigationElementInterface
     */
    public function setElementIcon(string $icon = null): NavigationElementInterface;

    /**
     * @return string|null
     */
    public function getElementIcon(): ?string;
}
