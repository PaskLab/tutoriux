<?php

namespace App\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;

/**
 * Class AppExtension
 * @package App\DependencyInjection
 */
class AppExtension extends Extension implements PrependExtensionInterface
{
    /**
     * @param array $configs
     * @param ContainerBuilder $container
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('app.metadata.title', $config['metadata']['title']);
        $container->setParameter('app.metadata.description', $config['metadata']['description']);
        $container->setParameter('app.metadata.keywords', $config['metadata']['keywords']);

        $container->setParameter('app.notification.default_expiration', $config['notification']['default_expiration']);
        $container->setParameter('app.log.default_expiration', $config['log']['default_expiration']);

        $container->setParameter('app.emails.feedback', $config['emails']['feedback']);
        $container->setParameter('app.emails.system_email', $config['emails']['system_email']);
    }

    /**
     * @param ContainerBuilder $container
     */
    public function prepend(ContainerBuilder $container)
    {
        $config = array(
            'filter_sets' => array(

                // Filters name must begin by 'media' if their handled by the MediaBundle

                'media_user_avatar_small_round' => array(
                    'quality' => 100,
                    'filters' => array(
                        'thumbnail' => array(
                            'size' => array(60, 60),
                            'mode' => 'outbound'
                        )
                    )
                ),
                'media_user_avatar_medium_round' => array(
                    'quality' => 100,
                    'filters' => array(
                        'thumbnail' => array(
                            'size' => array(80, 80),
                            'mode' => 'outbound'
                        )
                    )
                )
            )
        );

        $container->prependExtensionConfig('liip_imagine', $config);
    }
}