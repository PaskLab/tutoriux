<?php

namespace App\Services;

use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\RouterInterface;

/**
 * Class RouteResolver
 * @package App\Services
 */
class RouteResolver
{
    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @inheritdoc
     */
    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * @param int $sectionId
     * @param string|null $name
     * @param array $parameters
     * @return string
     * @throws \Exception
     */
    public function resolveSectionRoute(int $sectionId, string $name = null, array $parameters = []): string
    {
        $routes = $this->router->getRouteCollection();

        if (null == $name) {

            foreach ($routes as $routeName => $route) {
                if (0 === strpos($routeName, $sectionId.'_') && $route->getDefault('_tutoriuxEnabled')) {
                    $name = $route->getDefault('_canonical_route');
                    break;
                }
            }

            if (!$name) {
                throw new RouteNotFoundException('There is no route mapped to section id ['.$sectionId.'].');
            }

            if (preg_match_all('/{([^\}]+)}/', $route->getPath(), $parametersNames)) {
                foreach ($parametersNames[1] as $parameterName) {
                    if (!array_key_exists($parameterName, $route->getDefaults())) {
                        throw new \Exception('Parameters of default section route must have a default value. ('.$routeName.')');
                    }
                }
            }

        } else {
            $locale = (isset($parameters['_locale'])) ? $parameters['_locale']
                : $this->router->getContext()->getParameter('_locale');
            $name = $sectionId . '_' . $name . '.' . $locale;
            if (!$routes->get($name)) {
                throw new RouteNotFoundException(
                    'Route ' . $name . ' does not exist, check if section id ['.$sectionId.'] is mapped to this route.'
                );
            }
        }

        return $name;
    }
}