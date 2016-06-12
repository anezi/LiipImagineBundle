<?php

namespace Anezi\ImagineBundle\tests;

use Anezi\ImagineBundle\AneziImagineBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @covers Anezi\ImagineBundle\AneziImagineBundle
 */
class AneziImagineBundleTest extends \Phpunit_Framework_TestCase
{
    public function testSubClassOfBundle()
    {
        $this->assertInstanceOf('Symfony\Component\HttpKernel\Bundle\Bundle', new AneziImagineBundle());
    }

    public function testAddLoadersCompilerPassOnBuild()
    {
        $containerMock = $this->createContainerBuilderMock();
        $containerMock
            ->expects($this->atLeastOnce())
            ->method('getExtension')
            ->with('anezi_imagine')
            ->will($this->returnValue($this->createExtensionMock()));
        $containerMock
            ->expects($this->at(0))
            ->method('addCompilerPass')
            ->with($this->isInstanceOf('Anezi\ImagineBundle\DependencyInjection\Compiler\LoadersCompilerPass'));

        $container = new ContainerBuilder();

        $bundle = new AneziImagineBundle();

        $bundle->build($containerMock);
    }

    public function testAddFiltersCompilerPassOnBuild()
    {
        $containerMock = $this->createContainerBuilderMock();
        $containerMock
            ->expects($this->atLeastOnce())
            ->method('getExtension')
            ->with('anezi_imagine')
            ->will($this->returnValue($this->createExtensionMock()));
        $containerMock
            ->expects($this->at(1))
            ->method('addCompilerPass')
            ->with($this->isInstanceOf('Anezi\ImagineBundle\DependencyInjection\Compiler\FiltersCompilerPass'));

        $bundle = new AneziImagineBundle();

        $bundle->build($containerMock);
    }

    public function testAddPostProcessorsCompilerPassOnBuild()
    {
        $containerMock = $this->createContainerBuilderMock();
        $containerMock
            ->expects($this->atLeastOnce())
            ->method('getExtension')
            ->with('anezi_imagine')
            ->will($this->returnValue($this->createExtensionMock()));
        $containerMock
            ->expects($this->at(2))
            ->method('addCompilerPass')
            ->with($this->isInstanceOf('Anezi\ImagineBundle\DependencyInjection\Compiler\PostProcessorsCompilerPass'));

        $bundle = new AneziImagineBundle();

        $bundle->build($containerMock);
    }

    public function testAddResolversCompilerPassOnBuild()
    {
        $containerMock = $this->createContainerBuilderMock();
        $containerMock
            ->expects($this->atLeastOnce())
            ->method('getExtension')
            ->with('anezi_imagine')
            ->will($this->returnValue($this->createExtensionMock()));
        $containerMock
            ->expects($this->at(3))
            ->method('addCompilerPass')
            ->with($this->isInstanceOf('Anezi\ImagineBundle\DependencyInjection\Compiler\ResolversCompilerPass'));

        $bundle = new AneziImagineBundle();

        $bundle->build($containerMock);
    }

    public function testAddWebPathResolverFactoryOnBuild()
    {
        $extensionMock = $this->createExtensionMock();
        $extensionMock
            ->expects($this->at(0))
            ->method('addResolverFactory')
            ->with($this->isInstanceOf('Anezi\ImagineBundle\DependencyInjection\Factory\Resolver\WebPathResolverFactory'));

        $containerMock = $this->createContainerBuilderMock();
        $containerMock
            ->expects($this->atLeastOnce())
            ->method('getExtension')
            ->with('anezi_imagine')
            ->will($this->returnValue($extensionMock));

        $bundle = new AneziImagineBundle();

        $bundle->build($containerMock);
    }

    public function testAddAwsS3ResolverFactoryOnBuild()
    {
        $extensionMock = $this->createExtensionMock();
        $extensionMock
            ->expects($this->at(1))
            ->method('addResolverFactory')
            ->with($this->isInstanceOf('Anezi\ImagineBundle\DependencyInjection\Factory\Resolver\AwsS3ResolverFactory'));

        $containerMock = $this->createContainerBuilderMock();
        $containerMock
            ->expects($this->atLeastOnce())
            ->method('getExtension')
            ->with('anezi_imagine')
            ->will($this->returnValue($extensionMock));

        $bundle = new AneziImagineBundle();

        $bundle->build($containerMock);
    }

    public function testAddStreamLoaderFactoryOnBuild()
    {
        $extensionMock = $this->createExtensionMock();
        $extensionMock
            ->expects($this->at(2))
            ->method('addLoaderFactory')
            ->with($this->isInstanceOf('Anezi\ImagineBundle\DependencyInjection\Factory\Loader\StreamLoaderFactory'));

        $containerMock = $this->createContainerBuilderMock();
        $containerMock
            ->expects($this->atLeastOnce())
            ->method('getExtension')
            ->with('anezi_imagine')
            ->will($this->returnValue($extensionMock));

        $bundle = new AneziImagineBundle();

        $bundle->build($containerMock);
    }

    public function testAddFilesystemLoaderFactoryOnBuild()
    {
        $extensionMock = $this->createExtensionMock();
        $extensionMock
            ->expects($this->at(3))
            ->method('addLoaderFactory')
            ->with($this->isInstanceOf('Anezi\ImagineBundle\DependencyInjection\Factory\Loader\FilesystemLoaderFactory'));

        $containerMock = $this->createContainerBuilderMock();
        $containerMock
            ->expects($this->atLeastOnce())
            ->method('getExtension')
            ->with('anezi_imagine')
            ->will($this->returnValue($extensionMock));

        $bundle = new AneziImagineBundle();

        $bundle->build($containerMock);
    }

    public function testAddFlysystemLoaderFactoryOnBuild()
    {
        $extensionMock = $this->createExtensionMock();
        $extensionMock
            ->expects($this->at(4))
            ->method('addLoaderFactory')
            ->with($this->isInstanceOf('Anezi\ImagineBundle\DependencyInjection\Factory\Loader\FlysystemLoaderFactory'));

        $containerMock = $this->createContainerBuilderMock();
        $containerMock
            ->expects($this->atLeastOnce())
            ->method('getExtension')
            ->with('anezi_imagine')
            ->will($this->returnValue($extensionMock));

        $bundle = new AneziImagineBundle();

        $bundle->build($containerMock);
    }

    protected function createContainerBuilderMock()
    {
        return $this->getMock('Symfony\Component\DependencyInjection\ContainerBuilder', [], [], '', false);
    }

    protected function createExtensionMock()
    {
        $methods = [
            'getNamespace', 'addResolverFactory', 'addLoaderFactory',
        ];

        return $this->getMock('Anezi\ImagineBundle\DependencyInjection\AneziImagineExtension', $methods, [], '', false);
    }
}
