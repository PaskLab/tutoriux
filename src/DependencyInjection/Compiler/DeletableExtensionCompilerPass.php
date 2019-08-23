<?php

namespace App\DependencyInjection\Compiler;

use App\Services\Deletable;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class DeletableExtensionCompilerPass
 * @package App\DependencyInjection\Compiler
 */
class DeletableExtensionCompilerPass implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(Deletable::class)) {
            return;
        }

        $definition = $container->getDefinition(Deletable::class);
        $taggedServices = $container->findTaggedServiceIds('entity.deletable');

        foreach ($taggedServices as $id => $attributes) {
            $definition->addMethodCall('addListener', array(new Reference($id), $attributes[0]['entity']));
        }

    }
}
