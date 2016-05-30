<?php

namespace Anezi\ImagineBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class FiltersCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $tags = $container->findTaggedServiceIds('anezi_imagine.filter.loader');

        if (count($tags) > 0 && $container->hasDefinition('anezi_imagine.filter.manager')) {
            $manager = $container->getDefinition('anezi_imagine.filter.manager');

            foreach ($tags as $id => $tag) {
                $manager->addMethodCall('addLoader', array($tag[0]['loader'], new Reference($id)));
            }
        }
    }
}
