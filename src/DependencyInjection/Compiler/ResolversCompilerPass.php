<?php

namespace Anezi\ImagineBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class ResolversCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $tags = $container->findTaggedServiceIds('anezi_imagine.cache.resolver');

        if (count($tags) > 0 && $container->hasDefinition('anezi_imagine.cache.manager')) {
            $manager = $container->getDefinition('anezi_imagine.cache.manager');

            foreach ($tags as $id => $tag) {
                $manager->addMethodCall('addResolver', [$tag[0]['resolver'], new Reference($id)]);
            }
        }
    }
}
