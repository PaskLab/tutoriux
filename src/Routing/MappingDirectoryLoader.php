<?php

namespace App\Routing;

use Symfony\Component\Routing\RouteCollection,
    Symfony\Component\Config\Loader\FileLoader,
    Symfony\Component\Config\Resource\DirectoryResource;

/**
 * Class MappingDirectoryLoader
 * @package App\Routing
 */
class MappingDirectoryLoader extends FileLoader
{
    /**
     * @param mixed $file
     * @param null $type
     * @return RouteCollection
     * @throws \Symfony\Component\Config\Exception\FileLoaderImportCircularReferenceException
     * @throws \Symfony\Component\Config\Exception\LoaderLoadException
     */
    public function load($file, $type = null)
    {
        $path = $this->locator->locate($file);

        $collection = new RouteCollection();
        $collection->addResource(new DirectoryResource($path));

        foreach (scandir($path) as $dir) {
            if ('.' !== $dir[0]) {
                $this->setCurrentDir($path);
                $subPath = $path.'/'.$dir;
                $subType = 'mapping';

                if (is_dir($subPath)) {
                    $subPath .= '/';
                    $subType = 'mapping_directory';
                }

                $subCollection = $this->import($subPath, $subType, false, $path);
                $collection->addCollection($subCollection);
            }
        }

        return $collection;
    }

    /**
     * @param mixed $resource
     * @param null $type
     * @return bool
     */
    public function supports($resource, $type = null)
    {
        // only when type is forced to 'mapping_directory', not to conflict with other loaders
        return 'mapping_directory' === $type;
    }
}