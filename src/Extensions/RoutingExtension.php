<?php

namespace App\Extensions;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bridge\Twig\Extension\RoutingExtension as BaseRoutingExtension;
use App\Services\RouterAutoParametersHandler;
use Symfony\Component\Routing\RouterInterface;

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

    /**
     * @inheritdoc
     */
    public function __construct(UrlGeneratorInterface $generator, RouterAutoParametersHandler $autoParametersHandler,
                                RouterInterface $router)
    {
        $this->generator = $generator;
        $this->autoParametersHandler = $autoParametersHandler;
        $this->router = $router;

        parent::__construct($generator);
    }

    /**
     * Overridden to handle automatics parameters.
     *
     * @inheritdoc
     */
    public function getPath($name, $parameters = array(), $relative = false)
    {
        $route = $this->router->getRouteCollection()->get($name);

        if ($route && preg_match('#\{sectionId\}#', $route->getPath())) {
            $parameters = $this->autoParametersHandler->inject($parameters);
        }

        return $this->generator->generate($name, $parameters, $relative ? UrlGeneratorInterface::RELATIVE_PATH : UrlGeneratorInterface::ABSOLUTE_PATH);
    }

    /**
     * Overridden to handle automatics parameters.
     *
     * @inheritdoc
     */
    public function getUrl($name, $parameters = array(), $schemeRelative = false)
    {
        $route = $this->router->getRouteCollection()->get($name);

        if ($route && preg_match('#\{sectionId\}#', $route->getPath())) {
            $parameters = $this->autoParametersHandler->inject($parameters);
        }

        return $this->generator->generate($name, $parameters, $schemeRelative ? UrlGeneratorInterface::NETWORK_PATH : UrlGeneratorInterface::ABSOLUTE_URL);
    }
}