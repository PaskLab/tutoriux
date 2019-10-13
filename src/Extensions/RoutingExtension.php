<?php

namespace App\Extensions;

use App\Services\RouteResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bridge\Twig\Extension\RoutingExtension as BaseRoutingExtension;
use App\Services\RouterAutoParametersHandler;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouterInterface;
use Twig\TwigFunction;

/**
 * Class RoutingExtension
 * @package App\Extensions
 */
class RoutingExtension extends BaseRoutingExtension
{
    /**
     * @var UrlGeneratorInterface
     */
    private $generator;

    /**
     * @var RouterAutoParametersHandler
     */
    private $autoParametersHandler;

    /**
     * @var RouterInterface
     */
    private $router;

    /** @var RouteResolver */
    private $routeResolver;

    /**
     * @inheritdoc
     */
    public function __construct(UrlGeneratorInterface $generator, RouterAutoParametersHandler $autoParametersHandler,
                                RouterInterface $router, RouteResolver $routeResolver)
    {
        $this->generator = $generator;
        $this->autoParametersHandler = $autoParametersHandler;
        $this->router = $router;
        $this->routeResolver = $routeResolver;

        parent::__construct($generator);
    }

    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return array An array of functions
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('url', [$this, 'getUrl'], ['is_safe_callback' => [$this, 'isUrlGenerationSafe']]),
            new TwigFunction('path', [$this, 'getPath'], ['is_safe_callback' => [$this, 'isUrlGenerationSafe']]),
            new TwigFunction('sectionPath', [$this, 'getSectionPath'], ['is_safe_callback' => [$this, 'isUrlGenerationSafe']]),
            new TwigFunction('sectionUrl', [$this, 'getSectionUrl'], ['is_safe_callback' => [$this, 'isUrlGenerationSafe']])
        ];
    }

    /**
     * Overridden to handle automatics parameters.
     *
     * @inheritdoc
     */
    public function getPath($name, $parameters = [], $relative = false)
    {
        $route = $this->router->getRouteCollection()->get($name);

        return $this->generator->generate($name, $this->injectAutoParameters($route, $parameters),
            $relative ? UrlGeneratorInterface::RELATIVE_PATH : UrlGeneratorInterface::ABSOLUTE_PATH);
    }

    /**
     * Overridden to handle automatics parameters.
     *
     * @inheritdoc
     */
    public function getUrl($name, $parameters = [], $schemeRelative = false)
    {
        $route = $this->router->getRouteCollection()->get($name);

        return $this->generator->generate($name, $this->injectAutoParameters($route, $parameters),
            $schemeRelative ? UrlGeneratorInterface::NETWORK_PATH : UrlGeneratorInterface::ABSOLUTE_URL);
    }

    /**
     * Inject the current section id if not defined in the $parameters array
     *
     * @param Route $route
     * @param array $parameters
     * @return array
     */
    private function injectAutoParameters(?Route $route, array $parameters): array
    {
        if ($route && preg_match('#\{sectionId\}#', $route->getPath()) && !isset($parameters['sectionId'])) {
            return $this->autoParametersHandler->inject($parameters);
        }

        return $parameters;
    }

    /**
     * @param int $sectionId
     * @param string|null $name
     * @param array $parameters
     * @param bool $relative
     * @return string
     * @throws \Exception
     */
    public function getSectionPath(int $sectionId, string $name = null, array $parameters = [], bool $relative = false)
    {
        return $this->generator->generate($this->routeResolver->resolveSectionRoute($sectionId, $name, $parameters), $parameters,
            $relative ? UrlGeneratorInterface::RELATIVE_PATH : UrlGeneratorInterface::ABSOLUTE_PATH);
    }

    /**
     * @param int $sectionId
     * @param string $name
     * @param array $parameters
     * @param bool $schemeRelative
     * @return string
     * @throws \Exception
     */
    public function getSectionUrl(int $sectionId, string $name, array $parameters = [], bool $schemeRelative = false)
    {
        return $this->generator->generate($this->routeResolver->resolveSectionRoute($sectionId, $name, $parameters), $parameters,
            $schemeRelative ? UrlGeneratorInterface::NETWORK_PATH : UrlGeneratorInterface::ABSOLUTE_URL);
    }
}