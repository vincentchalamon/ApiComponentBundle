<?php

namespace Silverback\ApiComponentBundle\DependencyInjection;

use Doctrine\Common\Persistence\ObjectManager;
use Silverback\ApiComponentBundle\Factory\Component\AbstractComponentFactory;
use Silverback\ApiComponentBundle\Factory\Component\ComponentFactoryInterface;
use Silverback\ApiComponentBundle\Form\FormTypeInterface;
use Silverback\ApiComponentBundle\Form\Handler\FormHandlerInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\Reference;

class SilverbackApiComponentExtension extends Extension implements PrependExtensionInterface
{
    /**
     * @param array $configs
     * @param ContainerBuilder $container
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $this->loadServiceConfig($container);
    }

    /**
     * @param ContainerBuilder $container
     * @throws \Exception
     */
    private function loadServiceConfig(ContainerBuilder $container)
    {
        $loader = new PhpFileLoader(
            $container,
            new FileLocator(__DIR__.'/../Resources/config')
        );
        $loader->load('services.php');

        $container->registerForAutoconfiguration(FormHandlerInterface::class)
            ->addTag('silverback_api_component.form_handler')
            ->setLazy(true)
        ;

        $container->registerForAutoconfiguration(FormTypeInterface::class)
            ->addTag('silverback_api_component.form_type')
        ;

        $container->registerForAutoconfiguration(ComponentFactoryInterface::class)
            ->setParent(AbstractComponentFactory::class)
        ;

        $container->register(AbstractComponentFactory::class)
            ->setAbstract(true)
            ->addArgument(new Reference(ObjectManager::class))
        ;
    }

    /**
     * @param ContainerBuilder $container
     * @throws \Symfony\Component\DependencyInjection\Exception\InvalidArgumentException
     */
    public function prepend(ContainerBuilder $container)
    {
        $bundles = $container->getParameter('kernel.bundles');

        if (isset($bundles['LiipImagineBundle'])) {
            $container->prependExtensionConfig('liip_imagine', [
                'loaders' => [
                    'default' => [
                        'filesystem' => [
                            'data_root' => '%kernel.project_dir%/public'
                        ]
                    ]
                ],
                'filter_sets' => [
                    'placeholder' => [
                        'jpeg_quality' => 5,
                        'png_compression_level' => 9,
                        'filters' => [
                            'thumbnail' => [
                                'size' => [120, 120],
                                'mode' => 'outbound'
                            ]
                        ]
                    ],
                    'thumbnail' => [
                        'jpeg_quality' => 95,
                        'filters' => [
                            'upscale' => [
                                'min' => [636, 636]
                            ],
                            'thumbnail' => [
                                'size' => [636, 636],
                                'mode' => 'inset',
                                'allow_upscale' => true
                            ]
                        ]
                    ]
                ]
            ]);
        }
    }
}
