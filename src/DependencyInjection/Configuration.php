<?php

namespace Anezi\ImagineBundle\DependencyInjection;

use Anezi\ImagineBundle\DependencyInjection\Factory\Loader\LoaderFactoryInterface;
use Anezi\ImagineBundle\DependencyInjection\Factory\Resolver\ResolverFactoryInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * @var ResolverFactoryInterface[]
     */
    protected $resolversFactories;

    /**
     * @var LoaderFactoryInterface[]
     */
    protected $loadersFactories;

    /**
     * @param ResolverFactoryInterface[] $resolversFactories
     * @param LoaderFactoryInterface[]   $loadersFactories
     */
    public function __construct(array $resolversFactories, array $loadersFactories)
    {
        $this->resolversFactories = $resolversFactories;
        $this->loadersFactories = $loadersFactories;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('anezi_imagine', 'array');

        $resolversPrototypeNode = $rootNode
            ->children()
                ->arrayNode('resolvers')
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                        ->performNoDeepMerging();
        $this->addResolversSections($resolversPrototypeNode);

        $loadersPrototypeNode = $rootNode
            ->children()
                ->arrayNode('loaders')
                    ->useAttributeAsKey('name')
                    ->prototype('array');
        $this->addLoadersSections($loadersPrototypeNode);

        $rootNode
            ->beforeNormalization()
                ->ifTrue(function ($v) {
                    return
                        empty($v['loaders']) ||
                        empty($v['loaders']['default']) ||
                        empty($v['resolvers']) ||
                        empty($v['resolvers']['default']);
                })
                ->then(function ($v) {
                    if (empty($v['loaders'])) {
                        $v['loaders'] = [];
                    }

                    if (false === is_array($v['loaders'])) {
                        throw new \LogicException('Loaders has to be array');
                    }

                    if (false === array_key_exists('default', $v['loaders'])) {
                        $v['loaders']['default'] = ['filesystem' => null];
                    }

                    if (empty($v['resolvers'])) {
                        $v['resolvers'] = [];
                    }

                    if (false === is_array($v['resolvers'])) {
                        throw new \LogicException('Resolvers has to be array');
                    }

                    if (false === array_key_exists('default', $v['resolvers'])) {
                        $v['resolvers']['default'] = ['web_path' => null];
                    }

                    return $v;
                })
            ->end();

        $rootNode
            ->fixXmlConfig('filter_set', 'filter_sets')
            ->children()
                ->scalarNode('driver')->defaultValue('gd')
                    ->validate()
                        ->ifTrue(function ($v) {
                            return !in_array($v, ['gd', 'imagick', 'gmagick'], true);
                        })
                        ->thenInvalid('Invalid imagine driver specified: %s')
                    ->end()
                ->end()
                ->scalarNode('cache')->defaultValue('default')->end()
                ->scalarNode('cache_base_path')->defaultValue('')->end()
                ->scalarNode('data_loader')->defaultValue('default')->end()
                ->scalarNode('default_image')->defaultNull()->end()
                ->arrayNode('filter_sets')
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                        ->fixXmlConfig('filter', 'filters')
                        ->children()
                            ->scalarNode('quality')->defaultValue(100)->end()
                            ->scalarNode('jpeg_quality')->defaultNull()->end()
                            ->scalarNode('png_compression_level')->defaultNull()->end()
                            ->scalarNode('png_compression_filter')->defaultNull()->end()
                            ->scalarNode('format')->defaultNull()->end()
                            ->booleanNode('animated')->defaultFalse()->end()
                            ->scalarNode('cache')->defaultNull()->end()
                            ->scalarNode('data_loader')->defaultNull()->end()
                            ->scalarNode('default_image')->defaultNull()->end()
                            ->arrayNode('filters')
                                ->useAttributeAsKey('name')
                                ->prototype('array')
                                    ->useAttributeAsKey('name')
                                    ->prototype('variable')->end()
                                ->end()
                            ->end()
                            ->arrayNode('post_processors')
                                ->defaultValue([])
                                ->useAttributeAsKey('name')
                                ->prototype('array')
                                    ->useAttributeAsKey('name')
                                    ->prototype('variable')->end()
                                ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ->end();

        return $treeBuilder;
    }

    /**
     * @param ArrayNodeDefinition $resolversPrototypeNode
     */
    protected function addResolversSections(ArrayNodeDefinition $resolversPrototypeNode)
    {
        foreach ($this->resolversFactories as $factory) {
            $factory->addConfiguration(
                $resolversPrototypeNode->children()->arrayNode($factory->getName())
            );
        }
    }

    /**
     * @param ArrayNodeDefinition $resolversPrototypeNode
     */
    protected function addLoadersSections(ArrayNodeDefinition $resolversPrototypeNode)
    {
        foreach ($this->loadersFactories as $factory) {
            $factory->addConfiguration(
                $resolversPrototypeNode->children()->arrayNode($factory->getName())
            );
        }
    }
}
