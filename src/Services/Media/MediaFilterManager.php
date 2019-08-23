<?php

namespace App\Services\Media;

use Symfony\Component\HttpKernel\Exception\{NotFoundHttpException};
use Symfony\Component\DependencyInjection\ContainerInterface;

use Doctrine\ORM\EntityNotFoundException;

use Imagine\Exception\RuntimeException;

use Liip\ImagineBundle\Exception\Binary\Loader\NotLoadableException,
    Liip\ImagineBundle\Imagine\Data\DataManager,
    Liip\ImagineBundle\Imagine\Cache\CacheManager,
    Liip\ImagineBundle\Imagine\Filter\FilterManager;

class MediaFilterManager
{
    /**
     * @var ContainerInterface $container
     */
    private $container;

    /**
     * @var DataManager
     */
    private $dataManager;

    /**
     * @var CacheManager
     */
    private $cacheManager;

    /**
     * @var FilterManager
     */
    private $filterManager;

    /**
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Media Filter Manager constructor.
     *
     * @param DataManager $dataManager
     * @param CacheManager $cacheManager
     * @param FilterManager $filterManager
     */
    public function __construct(DataManager $dataManager, CacheManager $cacheManager, FilterManager $filterManager)
    {
        $this->dataManager = $dataManager;
        $this->cacheManager = $cacheManager;
        $this->filterManager = $filterManager;
    }

    /**
     * Create filter cache for given filter applied to a given image.
     *
     * @param string $path
     * @param string $filter
     * @throws EntityNotFoundException
     */
    public function applyFilter($path, $filter)
    {
        try {

            if (preg_match('#^media#i', $filter)) {
                $this->createBase($path);

                $basePath = preg_filter(
                    '#^(http://|https://)?([^/]+)+(.*)$#i',
                    '$3',
                    $this->cacheManager->resolve($path, 'base_crop')
                );
            } else {
                $basePath = $path;
            }

            if (!$this->cacheManager->isStored($path, $filter)) {
                try {
                    $binary = $this->dataManager->find($filter, $basePath);
                } catch (NotLoadableException $e) {
                    throw new NotFoundHttpException('Source image could not be found', $e);
                }

                $this->cacheManager->store(
                    $this->filterManager->applyFilter($binary, $filter),
                    $path,
                    $filter
                );
            }

        } catch (RuntimeException $e) {
            throw new \RuntimeException(sprintf('Unable to create image for path "%s" and filter "%s". Message was "%s"', $path, $filter, $e->getMessage()), 0, $e);
        }
    }

    /**
     * @param $path
     * @throws \RuntimeException
     * @throws \Doctrine\ORM\EntityNotFoundException
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function createBase($path)
    {
        try {

            if (!$this->cacheManager->isStored($path, 'base') || !$this->cacheManager->isStored($path, 'base_crop')) {

                try {
                    $binary = $this->dataManager->find('base', $path);
                } catch (NotLoadableException $e) {

                    throw new NotFoundHttpException('Source image could not be found', $e);
                }

                $path = (substr($path, 0, 1) == '/') ? substr($path, 1) : $path;
                $uploadDir = substr($this->container->getParameter('doctrine_behaviors.uploadable.upload_web_dir'), 1) . '/';

                $media = $this->container->get('doctrine')->getRepository('MediaBundle:Media')->findOneBy([
                    'mediaPath' => str_replace($uploadDir, '', $path)
                ]);

                if (!$media) {
                    throw new EntityNotFoundException(sprintf('Unable to find a record for image path "%s" and filter "%s".', $path, 'base'));
                }

                $baseSize = $this->container->getParameter('media.images.baseSize');
                $width = $media->getResizeWidth();
                $height = $media->getResizeHeight();
                $filters = [];

                if (empty($width) || empty($height)) {
                    $filters['thumbnail'] = [
                        'size' => [$baseSize['width'], $baseSize['height']],
                        'mode' => 'inset'
                    ];
                } else {

                    $size = [$width, $height];

                    if ($width > $baseSize['width'] || $height > $baseSize['height']) {
                        $size = [$baseSize['width'], $baseSize['height']];
                    }

                    $filters['thumbnail'] = [
                        'size' => $size,
                        'mode' => 'outbound'
                    ];
                }

                $this->cacheManager->store(
                    $this->filterManager->applyFilter($binary, 'base', [
                        'filters' => $filters
                    ]),
                    $path,
                    'base'
                );

                try {
                    $basePath = preg_filter(
                        '#^(http://|https://)?([^/]+)+(.*)$#i',
                        '$3',
                        $this->cacheManager->resolve($path, 'base')
                    );
                    $binary = $this->dataManager->find('base', $basePath);
                } catch (NotLoadableException $e) {
                    throw new NotFoundHttpException('Source image could not be found', $e);
                }

                if ($media->getCropJson()) {
                    $cropInfo = json_decode($media->getCropJson(), true);
                    $filters['crop'] = [
                        'start' => [
                            round((float) $cropInfo['x1'] * $media->getResizeWidth()),
                            round((float) $cropInfo['y1'] * $media->getResizeHeight())
                        ],
                        'size' => [$media->getCropWidth(), $media->getCropHeight()]
                    ];
                }

                $this->cacheManager->store(
                    $this->filterManager->applyFilter($binary, 'base_crop', [
                        'filters' => $filters
                    ]),
                    $path,
                    'base_crop'
                );
            }
        } catch (RuntimeException $e) {
            throw new \RuntimeException(sprintf('Unable to create image for path "%s" and filter "%s". Message was "%s"', $path, 'base', $e->getMessage()), 0, $e);
        }
    }
}