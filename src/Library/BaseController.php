<?php

namespace App\Library;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Services\ApplicationCore;
use App\Entity\Section;

/**
 * Class BaseController
 * @package App\Library
 */
abstract class BaseController extends AbstractController
{
    const FLASH_SUCCESS = 'success';
    const FLASH_ERROR = 'error';

    /**
     * @var ApplicationCore
     */
    private $applicationCore;

    /**
     * ApplicationController constructor.
     * @param ApplicationCore $applicationCore
     */
    public function __construct(ApplicationCore $applicationCore)
    {
        $this->applicationCore = $applicationCore;
    }

    /**
     * @return ApplicationCore
     */
    protected function getApplicationCore()
    {
        return $this->applicationCore;
    }

    /**
     * Get the current section entity
     *
     * @return Section
     */
    protected function getSection()
    {
        return $this->getApplicationCore()->getSection();
    }

    /**
     * @param null $manager
     * @return \Doctrine\Common\Persistence\ObjectManager
     */
    protected function getEm($manager = null)
    {
        return $this->getDoctrine()->getManager($manager);
    }

    /**
     * @param $name
     * @return \Doctrine\Common\Persistence\ObjectRepository
     */
    protected function getRepository($name)
    {
        return $this->getDoctrine()->getRepository($name);
    }

    /**
     * @param $type
     * @param $message
     */
    protected function setFlash($type, $message)
    {
        $this->get('session')->getFlashBag()->set($type, $message);
    }

    /**
     * @param string $type
     * @param string $message
     */
    protected function addFlash($type, $message)
    {
        $this->get('session')->getFlashBag()->add($type, $message);
    }

    /**
     * @param $type
     * @return bool
     */
    protected function hasFlash($type)
    {
        return $this->container->get('session')->getFlashBag()->has($type);
    }

    /**
     * @param $type
     * @param array $default
     * @return array
     */
    protected function getFlash($type, array $default = array())
    {
        return $this->container->get('session')->getFlashBag()->get($type, $default);
    }

    /**
     * @param $message
     */
    protected function setFlashSuccess($message)
    {
        $this->setFlash(BaseController::FLASH_SUCCESS, $message);
    }

    /**
     * @param $message
     */
    protected function addFlashSuccess($message)
    {
        $this->addFlash(BaseController::FLASH_SUCCESS, $message);
    }

    /**
     * @param $message
     */
    protected function addFlashError($message)
    {
        $this->addFlash(BaseController::FLASH_ERROR, $message);
    }

    /**
     * @param $element
     */
    protected function addNavigationElement($element)
    {
        trigger_error('addNavigationElement is deprecated. Use pushNavigationElement instead.', E_USER_DEPRECATED);

        $this->pushNavigationElement($element);
    }

    /**
     * @param $element
     * @throws \Exception
     */
    protected function pushNavigationElement($element)
    {
        $this->getApplicationCore()->addNavigationElement($element);
    }

    /**
     * Helper method to create a navigation element
     *
     * @param string $name
     * @param string $route
     * @param array  $routeParams
     *
     * @return NavigationElement
     */
    protected function createNavigationElement($name, $route, $routeParams = array(), $icon = null)
    {
        $navigationElement = new NavigationElement();
        $navigationElement->setContainer($this->container)
            ->setName($name)
            ->setRouteFrontend($route)
            ->setRouteFrontendParams($routeParams)
            ->setIcon($icon);

        return $navigationElement;
    }

    /**
     * @param $name
     * @param $route
     * @param array $routeParams
     * @param null $icon
     * @throws \Exception
     */
    protected function createAndPushNavigationElement($name, $route, $routeParams = array(), $icon = null)
    {
        $navigationElement = $this->createNavigationElement($name, $route, $routeParams, $icon);
        $this->pushNavigationElement($navigationElement);
    }
}
