<?php

namespace Anezi\ImagineBundle\DependencyInjection\Factory\Loader;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\DefinitionDecorator;

class FileSystemLoaderFactory implements LoaderFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function create(ContainerBuilder $container, $loaderName, array $config)
    {
        $loaderDefinition = new DefinitionDecorator('anezi_imagine.binary.loader.prototype.filesystem');
        $loaderDefinition->replaceArgument(2, $config['data_root']);
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
        return 'filesystem';
    }

    /**
     * {@inheritdoc}
     */
    public function addConfiguration(ArrayNodeDefinition $builder)
    {
        $builder
            ->children()
                ->scalarNode('data_root')->defaultValue('%kernel.root_dir%/../web')->cannotBeEmpty()->end()
            ->end();
    }
}
