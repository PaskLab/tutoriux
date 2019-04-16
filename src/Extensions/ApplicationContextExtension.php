<?php

namespace App\Extensions;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\RequestStack;

/**
 * This class purpose is to provide context information in twig templates.
 *
 * Class ApplicationContextExtension
 * @package App\Extensions
 */
class ApplicationContextExtension extends AbstractExtension
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * ApplicationContextExtension constructor.
     * @param RequestStack $requestStack
     */
    public function __construct(RequestStack $requestStack)
    {
        $this->request = $requestStack->getCurrentRequest();
    }

    /**
     * Get Functions
     *
     * @return array
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('get_bundle_name', [$this, 'getBundleName'], ['needs_environment' => true]),
            new TwigFunction('get_controller_name', [$this, 'getControllerName'], ['needs_environment' => true]),
            new TwigFunction('get_action_name', [$this, 'getActionName'], ['needs_environment' => true]),
        ];
    }

    /**
     * Get current bundle name
     *
     * @return string|null
     */
    public function getBundleName()
    {
        $controller = $this->request->get('_controller');

        $matches = [];

        if (preg_match('#\\\([a-zA-Z]*)Bundle#', $controller, $matches)) {
            return strtolower($matches[1]);
        }

        return 'unknown';
    }

    /**
     * Get current controller name
     *
     * @return string|null
     */
    public function getControllerName()
    {
        $controller = $this->request->get('_controller');

        $matches = [];

        if (preg_match('#(.*)\\\([a-zA-Z]*)Controller(.*)#', $controller, $matches)) {
            return strtolower($matches[2]);
        }

        return 'unknown';
    }

    /**
     * @return mixed
     */
    public function getActionName()
    {
        $controller = $this->request->get('_controller');

        $matches = [];

        if (preg_match('#::([a-zA-Z]*)Action#', $controller, $matches)) {
            return $matches[1];
        }

        return 'unknown';
    }

    /**
     * Get Name
     *
     * @return string
     */
    public function getName()
    {
        return 'application_context_extension';
    }
}
