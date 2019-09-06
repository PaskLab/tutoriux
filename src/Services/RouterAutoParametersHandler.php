<?php

namespace App\Services;

use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class RouterAutoParametersHandler
 * @package App\Services
 */
class RouterAutoParametersHandler
{
    /**
     * @var RequestStack
     */
    private $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    /**
     * @return array
     */
    public function getAutoParameters()
    {
        $parameters = [];
        $request = $this->requestStack->getMasterRequest();

        if ($request->get('_tutoriuxEnabled', false)) {
            $tutoriuxRequest = $request->get('_tutoriuxRequest');
            $parameters['sectionId'] = $tutoriuxRequest['sectionId'];
        }

        return $parameters;
    }

    /**
     * This inject auto parameters into a given parameter array
     *
     * @param array $parameters
     *
     * @return array
     */
    public function inject($parameters)
    {
        $parameters = array_merge($this->getAutoParameters(), $parameters);

        return $parameters;
    }
}
