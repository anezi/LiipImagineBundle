<?php

namespace Anezi\ImagineBundle\DependencyInjection\Factory\Loader;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Reference;

class FlysystemLoaderFactory implements LoaderFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function create(ContainerBuilder $container, $loaderName, array $config)
    {
        $loaderDefinition = new DefinitionDecorator('anezi_imagine.binary.loader.prototype.flysystem');
        $loaderDefinition->replaceArgument(1, new Reference($config['filesystem_service']));
        $loaderDefinition->addTag('anezi_imagine.binary.loader', [
            'loader' => $loaderName,
        ]);
        $loaderId = 'anezi_imagine.binary.loader.'.$loaderName;

        $container->setDefinition($loaderId, $loaderDefinition);

        return $loaderId;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'flysystem';
    }

    /**
     * {@inheritdoc}
     */
    public function addConfiguration(ArrayNodeDefinition $builder)
    {
        $builder
            ->children()
                ->scalarNode('filesystem_service')->isRequired()->cannotBeEmpty()->end()
            ->end();
    }
}
