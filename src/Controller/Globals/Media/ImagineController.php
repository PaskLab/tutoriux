<?php

namespace App\Controller\Globals\Media;

use Symfony\Component\HttpFoundation\{RedirectResponse, Request};
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\EntityNotFoundException;
use Liip\ImagineBundle\Controller\ImagineController as BaseImagineController,
    Liip\ImagineBundle\Imagine\Cache\SignerInterface,
    Liip\ImagineBundle\Imagine\Data\DataManager,
    Liip\ImagineBundle\Service\FilterService;

/**
 * Class ImagineController
 * @package App\Controller\Component\Media
 */
class ImagineController extends BaseImagineController
{
    /**
     * @var ContainerInterface $container
     */
    private $container;

    /**
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * ImagineController constructor.
     * @param FilterService $filterService
     * @param DataManager $dataManager
     * @param SignerInterface $signer
     */
    public function __construct(FilterService $filterService, DataManager $dataManager, SignerInterface $signer)
    {
        parent::__construct($filterService, $dataManager, $signer);
    }

    /**
     * This action applies a given filter to a given image, optionally saves the image and outputs it to the browser at the same time.
     *
     * @param Request $request
     * @param string $path
     * @param string $filter
     * @return RedirectResponse
     * @throws EntityNotFoundException
     */
    public function filterAction(Request $request, $path, $filter)
    {
        $this->container->get('media.filter_manager')->applyFilter($path, $filter);

        return new RedirectResponse($this->container->get('liip_imagine.cache.manager')->resolve($path, $filter), 301);
    }

    /**
     * @param Request $request
     * @param $path
     * @throws EntityNotFoundException
     */
    public function createBase(Request $request, $path)
    {
        $this->container->get('media.filter_manager')->createBase($path);
    }
}