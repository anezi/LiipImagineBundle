<?php

namespace Anezi\ImagineBundle\DependencyInjection;

use Anezi\ImagineBundle\DependencyInjection\Factory\Loader\LoaderFactoryInterface;
use Anezi\ImagineBundle\DependencyInjection\Factory\Resolver\ResolverFactoryInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * Class AneziImagineExtension.
 */
class AneziImagineExtension extends Extension
{
    /**
     * @var ResolverFactoryInterface[]
     */
    protected $resolversFactories = [];

    /**
     * @var LoaderFactoryInterface[]
     */
    protected $loadersFactories = [];

    /**
     * @param ResolverFactoryInterface $resolverFactory
     */
    public function addResolverFactory(ResolverFactoryInterface $resolverFactory)
    {
        $this->resolversFactories[$resolverFactory->getName()] = $resolverFactory;
    }

    /**
     * @param LoaderFactoryInterface $loaderFactory
     */
    public function addLoaderFactory(LoaderFactoryInterface $loaderFactory)
    {
        $this->loadersFactories[$loaderFactory->getName()] = $loaderFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfiguration(array $config, ContainerBuilder $container)
    {
        return new Configuration($this->resolversFactories, $this->loadersFactories);
    }

    /**
     * @see Symfony\Component\DependencyInjection\Extension.ExtensionInterface::load()
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $config = $this->processConfiguration(
            $this->getConfiguration($configs, $container),
            $configs
        );

        $this->loadResolvers($config['resolvers'], $container);
        $this->loadLoaders($config['loaders'], $container);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        $this->setFactories($container);

        if (interface_exists('Imagine\Image\Metadata\MetadataReaderInterface')) {
            $container->getDefinition('anezi_imagine.'.$config['driver'])->addMethodCall('setMetadataReader', [new Reference('anezi_imagine.meta_data.reader')]);
        } else {
            $container->removeDefinition('anezi_imagine.meta_data.reader');
        }

        $container->setAlias('anezi_imagine', new Alias('anezi_imagine.'.$config['driver']));

        $container->setParameter('anezi_imagine.cache.resolver.default', $config['cache']);

        $container->setParameter('anezi_imagine.default_image', $config['default_image']);

        $container->setParameter('anezi_imagine.filter_sets', $config['filter_sets']);
        $container->setParameter('anezi_imagine.binary.loader.default', $config['data_loader']);

        $container->setParameter('anezi_imagine.controller.filter_action', $config['controller']['filter_action']);
        $container->setParameter('anezi_imagine.controller.filter_runtime_action', $config['controller']['filter_runtime_action']);

        $resources = $container->hasParameter('twig.form.resources') ? $container->getParameter('twig.form.resources') : [];
        $resources[] = 'AneziImagineBundle:Form:form_div_layout.html.twig';
        $container->setParameter('twig.form.resources', $resources);
    }

    /**
     * @param array            $config
     * @param ContainerBuilder $container
     */
    protected function loadResolvers(array $config, ContainerBuilder $container)
    {
        foreach ($config as $resolverName => $resolverConfig) {
            $factoryName = key($resolverConfig);
            $factory = $this->resolversFactories[$factoryName];

            $factory->create($container, $resolverName, $resolverConfig[$factoryName]);
        }
    }

    /**
     * @param array            $config
     * @param ContainerBuilder $container
     */
    protected function loadLoaders(array $config, ContainerBuilder $container)
    {
        foreach ($config as $loaderName => $loaderConfig) {
            $factoryName = key($loaderConfig);
            $factory = $this->loadersFactories[$factoryName];

            $factory->create($container, $loaderName, $loaderConfig[$factoryName]);
        }
    }

    /**
     * @param ContainerBuilder $container
     */
    private function setFactories(ContainerBuilder $container)
    {
        $factories = [
            'anezi_imagine.mime_type_guesser' => ['Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesser', 'getInstance'],
            'anezi_imagine.extension_guesser' => ['Symfony\Component\HttpFoundation\File\MimeType\ExtensionGuesser', 'getInstance'],
        ];

        foreach ($factories as $service => $factory) {
            $definition = $container->getDefinition($service);
            if (method_exists($definition, 'setFactory')) {
                // to be inlined in services.xml when dependency on Symfony DependencyInjection is bumped to 2.6
                $definition->setFactory($factory);
            } else {
                // to be removed when dependency on Symfony DependencyInjection is bumped to 2.6
                $definition->setFactoryClass($factory[0]);
                $definition->setFactoryMethod($factory[1]);
            }
        }
    }
}
