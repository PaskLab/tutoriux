<?php

namespace App\Listeners;

use Symfony\Component\HttpKernel\Event\FilterControllerEvent,
    Symfony\Component\HttpKernel\HttpKernelInterface,
    Symfony\Component\EventDispatcher\EventSubscriberInterface,
    Symfony\Component\HttpKernel\KernelEvents,
    Symfony\Component\DependencyInjection\ContainerInterface;

use App\Library\BaseController,
    App\Services\ApplicationCore;

/**
 * Class ControllerListener
 * @package App\Listeners
 */
class ControllerListener implements EventSubscriberInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var ApplicationCore
     */
    private $applicationCore;

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [KernelEvents::CONTROLLER => [['onKernelController', 0]]];
    }

    /**
     * ControllerListener constructor.
     * @param ContainerInterface $container
     * @param ApplicationCore $applicationCore
     */
    public function __construct(ContainerInterface $container, ApplicationCore $applicationCore)
    {
        $this->container = $container;
        $this->applicationCore = $applicationCore;
    }

    /**
     * On Kernel Controller
     *
     * @param  FilterControllerEvent $event
     * @throws \Exception
     */
    public function onKernelController(FilterControllerEvent $event)
    {
        $controller = $event->getController();
        $controller = $controller[0];

        if (false == $controller instanceof BaseController) {
            return;
        }

        $request = $this->container->get('request_stack')->getCurrentRequest();
        $isTutoriuxEnabled = $request->get('_tutoriuxEnabled', false);

        if ('cms' == $request->get('_tutoriuxContext', 'site')) {
            // In CMS only, sectionId act as an hook to bootstrap tutoriux application core service
            if (!$isTutoriuxEnabled && $sectionId = $request->get('sectionId', false)) {
                $request->attributes->add([
                    '_tutoriuxEnabled' => true,
                    '_tutoriuxRequest' => [
                        'sectionId' => $sectionId,
                        'sectionSlug' => null,
                        'sectionsPath' => null
                    ]
                ]);
                $isTutoriuxEnabled = true;
            }
        }

        if (false == $isTutoriuxEnabled) {
            return;
        }

        // Initialization of cores stack in order of: system, application and controller
        if (HttpKernelInterface::MASTER_REQUEST === $event->getRequestType()) {
            $this->applicationCore->init();
        }
    }
}