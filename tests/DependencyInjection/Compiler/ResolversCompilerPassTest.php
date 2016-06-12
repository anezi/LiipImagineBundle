<?php

namespace Anezi\ImagineBundle\tests\DependencyInjection\Compiler;

use Anezi\ImagineBundle\DependencyInjection\Compiler\ResolversCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * @covers Anezi\ImagineBundle\DependencyInjection\Compiler\ResolversCompilerPass
 */
class ResolversCompilerPassTest extends \PHPUnit_Framework_TestCase
{
    public function testProcess()
    {
        $managerDefinition = new Definition();
        $resolverDefinition = new Definition();
        $resolverDefinition->addTag('anezi_imagine.cache.resolver', [
            'resolver' => 'foo',
        ]);

        $container = new ContainerBuilder();
        $container->setDefinition('anezi_imagine.cache.manager', $managerDefinition);
        $container->setDefinition('a.resolver', $resolverDefinition);

        $pass = new ResolversCompilerPass();

        //guard
        $this->assertCount(0, $managerDefinition->getMethodCalls());

        $pass->process($container);

        $this->assertCount(1, $managerDefinition->getMethodCalls());
    }
}
