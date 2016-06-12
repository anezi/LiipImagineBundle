<?php

namespace Anezi\ImagineBundle\tests\DependencyInjection\Factory\Loader;

use Anezi\ImagineBundle\DependencyInjection\Factory\Loader\FileSystemLoaderFactory;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @covers Anezi\ImagineBundle\DependencyInjection\Factory\Loader\FileSystemLoaderFactory<extended>
 */
class FileSystemLoaderFactoryTest extends \Phpunit_Framework_TestCase
{
    public function testImplementsLoaderFactoryInterface()
    {
        $rc = new \ReflectionClass('Anezi\ImagineBundle\DependencyInjection\Factory\Loader\FileSystemLoaderFactory');

        $this->assertTrue($rc->implementsInterface('Anezi\ImagineBundle\DependencyInjection\Factory\Loader\LoaderFactoryInterface'));
    }

    public function testCouldBeConstructedWithoutAnyArguments()
    {
        new FileSystemLoaderFactory();
    }

    public function testReturnExpectedName()
    {
        $loader = new FileSystemLoaderFactory();

        $this->assertSame('filesystem', $loader->getName());
    }

    public function testCreateLoaderDefinitionOnCreate()
    {
        $container = new ContainerBuilder();

        $loader = new FileSystemLoaderFactory();

        $loader->create($container, 'theLoaderName', [
            'data_root' => 'theDataRoot',
        ]);

        $this->assertTrue($container->hasDefinition('anezi_imagine.binary.loader.theloadername'));

        $loaderDefinition = $container->getDefinition('anezi_imagine.binary.loader.theloadername');
        $this->assertInstanceOf('Symfony\Component\DependencyInjection\DefinitionDecorator', $loaderDefinition);
        $this->assertSame('anezi_imagine.binary.loader.prototype.filesystem', $loaderDefinition->getParent());

        $this->assertSame('theDataRoot', $loaderDefinition->getArgument(2));
    }

    public function testProcessCorrectlyOptionsOnAddConfiguration()
    {
        $expectedDataRoot = 'theDataRoot';

        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('filesystem', 'array');

        $loader = new FileSystemLoaderFactory();
        $loader->addConfiguration($rootNode);

        $config = $this->processConfigTree($treeBuilder, [
            'filesystem' => [
                'data_root' => $expectedDataRoot,
            ],
        ]);

        $this->assertArrayHasKey('data_root', $config);
        $this->assertSame($expectedDataRoot, $config['data_root']);
    }

    public function testAddDefaultOptionsIfNotSetOnAddConfiguration()
    {
        $expectedDataRoot = '%kernel.root_dir%/../web';

        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('filesystem', 'array');

        $loader = new FileSystemLoaderFactory();
        $loader->addConfiguration($rootNode);

        $config = $this->processConfigTree($treeBuilder, [
            'filesystem' => [],
        ]);

        $this->assertArrayHasKey('data_root', $config);
        $this->assertSame($expectedDataRoot, $config['data_root']);
    }

    /**
     * @param TreeBuilder $treeBuilder
     * @param array       $configs
     *
     * @return array
     */
    protected function processConfigTree(TreeBuilder $treeBuilder, array $configs)
    {
        $processor = new Processor();

        return $processor->process($treeBuilder->buildTree(), $configs);
    }
}
