<?php

namespace App\Services;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * Class RouterInvalidator
 * @package App\Services
 */
class RouterInvalidator
{
    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var KernelInterface
     */
    private $kernel;

    /**
     * RouterInvalidator constructor.
     * @param RouterInterface $router
     * @param Filesystem $filesystem
     * @param KernelInterface $kernel
     */
    public function __construct(RouterInterface $router, Filesystem $filesystem, KernelInterface $kernel)
    {
        $this->router = $router;
        $this->filesystem = $filesystem;
        $this->kernel = $kernel;
    }

    /**
     * Invalidate routing and warm it up so it can be ready for next request
     */
    public function invalidate() : void
    {
        $cacheDir = $this->kernel->getCacheDir();

        foreach (array('matcher_cache_class', 'generator_cache_class') as $option) {
            $className = $this->router->getOption($option);
            $cacheFile = $cacheDir . DIRECTORY_SEPARATOR . $className . '.php';
            $this->filesystem->remove($cacheFile);
        }

        $this->router->warmUp($cacheDir);
    }
}