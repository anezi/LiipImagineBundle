<?php

namespace Anezi\ImagineBundle\tests\DependencyInjection\Compiler;

use Anezi\ImagineBundle\DependencyInjection\Compiler\LoadersCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * @covers Anezi\ImagineBundle\DependencyInjection\Compiler\LoadersCompilerPass
 */
class LoadersCompilerPassTest extends \PHPUnit_Framework_TestCase
{
    public function testProcess()
    {
        $managerDefinition = new Definition();
        $loaderDefinition = new Definition();
        $loaderDefinition->addTag('anezi_imagine.binary.loader', [
            'loader' => 'foo',
        ]);

        $container = new ContainerBuilder();
        $container->setDefinition('anezi_imagine.data.manager', $managerDefinition);
        $container->setDefinition('a.binary.loader', $loaderDefinition);

        $pass = new LoadersCompilerPass();

        //guard
        $this->assertCount(0, $managerDefinition->getMethodCalls());

        $pass->process($container);

        $this->assertCount(1, $managerDefinition->getMethodCalls());
    }
}
