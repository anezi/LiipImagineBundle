<?php

namespace Anezi\ImagineBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Compiler pass to register post_processors tagged with anezi_imagine.filter.post_processor.
 *
 * @author Konstantin Tjuterev <kostik.lv@gmail.com>
 */
class PostProcessorsCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $tags = $container->findTaggedServiceIds('anezi_imagine.filter.post_processor');

        if (count($tags) > 0 && $container->hasDefinition('anezi_imagine.filter.manager')) {
            $manager = $container->getDefinition('anezi_imagine.filter.manager');

            foreach ($tags as $id => $tag) {
                $manager->addMethodCall('addPostProcessor', [$tag[0]['post_processor'], new Reference($id)]);
            }
        }
    }
}
