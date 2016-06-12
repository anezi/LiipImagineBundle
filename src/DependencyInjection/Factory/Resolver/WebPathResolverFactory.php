<?php

namespace Anezi\ImagineBundle\DependencyInjection\Factory\Resolver;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\DefinitionDecorator;

class WebPathResolverFactory implements ResolverFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function create(ContainerBuilder $container, $resolverName, array $config)
    {
        $resolverDefinition = new DefinitionDecorator('anezi_imagine.cache.resolver.prototype.web_path');
        $resolverDefinition->replaceArgument(2, $config['web_root']);
        $resolverDefinition->replaceArgument(3, $config['cache_prefix']);
        $resolverDefinition->addTag('anezi_imagine.cache.resolver', [
            'resolver' => $resolverName,
        ]);
        $resolverId = 'anezi_imagine.cache.resolver.'.$resolverName;

        $container->setDefinition($resolverId, $resolverDefinition);

        return $resolverId;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'web_path';
    }

    /**
     * {@inheritdoc}
     */
    public function addConfiguration(ArrayNodeDefinition $builder)
    {
        $builder
            ->children()
                ->scalarNode('web_root')->defaultValue('%kernel.root_dir%/../web')->cannotBeEmpty()->end()
                ->scalarNode('cache_prefix')->defaultValue('media/cache')->cannotBeEmpty()->end()
            ->end();
    }
}
