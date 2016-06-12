<?php

namespace Anezi\ImagineBundle\tests\Imagine\Cache\Resolver;

use Anezi\ImagineBundle\Imagine\Cache\Resolver\ProxyResolver;
use Anezi\ImagineBundle\Imagine\Cache\Resolver\ResolverInterface;
use Anezi\ImagineBundle\Model\Binary;

/**
 * @covers Anezi\ImagineBundle\Imagine\Cache\Resolver\ProxyResolver
 */
class ProxyResolverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ResolverInterface
     */
    private $primaryResolver;

    /**
     * @var ProxyResolver
     */
    private $resolver;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->primaryResolver = $this->getMock('Anezi\ImagineBundle\Imagine\Cache\Resolver\ResolverInterface');

        $this->resolver = new ProxyResolver($this->primaryResolver, ['http://images.example.com']);
    }

    /**
     * @test 
     */
    public function testProxyCallAndRewriteReturnedUrlOnResolve()
    {
        $expectedPath = '/foo/bar/bazz.png';
        $expectedLoader = 'loader';
        $expectedFilter = 'test';

        $this->primaryResolver
            ->expects($this->once())
            ->method('resolve')
            ->with($expectedPath, $expectedFilter)
            ->will($this->returnValue('http://foo.com/thumbs/foo/bar/bazz.png'));

        $result = $this->resolver->resolve($expectedPath, $expectedLoader, $expectedFilter);

        $this->assertSame('http://images.example.com/thumbs/foo/bar/bazz.png', $result);
    }

    /**
     * @test
     */
    public function testProxyCallAndRewriteReturnedUrlEvenSchemesDiffersOnResolve()
    {
        $expectedPath = '/foo/bar/bazz.png';
        $expectedLoader = 'loader';
        $expectedFilter = 'test';

        $this->primaryResolver
            ->expects($this->once())
            ->method('resolve')
            ->with($expectedPath, $expectedFilter)
            ->will($this->returnValue('http://foo.com/thumbs/foo/bar/bazz.png'));

        $result = $this->resolver->resolve($expectedPath, $expectedLoader, $expectedFilter);

        $this->assertSame('http://images.example.com/thumbs/foo/bar/bazz.png', $result);
    }

    /**
     * @test
     */
    public function testProxyCallAndRewriteReturnedUrlWithMatchReplaceOnResolve()
    {
        $expectedPath = '/foo/bar/bazz.png';
        $expectedLoader = 'loader';
        $expectedFilter = 'test';

        $this->primaryResolver
            ->expects($this->once())
            ->method('resolve')
            ->with($expectedPath, $expectedFilter)
            ->will($this->returnValue('https://s3-eu-west-1.amazonaws.com/s3-cache.example.com/thumbs/foo/bar/bazz.png'));

        $this->resolver = new ProxyResolver($this->primaryResolver, [
            'https://s3-eu-west-1.amazonaws.com/s3-cache.example.com' => 'http://images.example.com',
        ]);

        $result = $this->resolver->resolve($expectedPath, $expectedLoader, $expectedFilter);

        $this->assertSame('http://images.example.com/thumbs/foo/bar/bazz.png', $result);
    }

    /**
     * @test
     */
    public function testProxyCallAndRewriteReturnedUrlWithRegExpOnResolve()
    {
        $expectedPath = '/foo/bar/bazz.png';
        $expectedLoader = 'loader';
        $expectedFilter = 'test';

        $this->primaryResolver
            ->expects($this->once())
            ->method('resolve')
            ->with($expectedPath, $expectedFilter)
            ->will($this->returnValue('http://foo.com/thumbs/foo/bar/bazz.png'));

        $this->resolver = new ProxyResolver($this->primaryResolver, [
            'regexp/http:\/\/.*?\//' => 'http://bar.com/',
        ]);

        $result = $this->resolver->resolve($expectedPath, $expectedLoader, $expectedFilter);

        $this->assertSame('http://bar.com/thumbs/foo/bar/bazz.png', $result);
    }

    /**
     * @test
     */
    public function testProxyCallAndReturnedValueOnIsStored()
    {
        $expectedPath = 'thePath';
        $expectedLoader = 'loader';
        $expectedFilter = 'theFilter';

        $this->primaryResolver
            ->expects($this->once())
            ->method('isStored')
            ->with($expectedPath, $expectedFilter)
            ->will($this->returnValue(true));

        $this->assertTrue($this->resolver->isStored($expectedPath, $expectedLoader, $expectedFilter));
    }

    /**
     * @test
     */
    public function testProxyCallOnStore()
    {
        $expectedPath = 'thePath';
        $expectedLoader = 'loader';
        $expectedFilter = 'theFilter';
        $expectedBinary = new Binary('aContent', 'image/png', 'png');

        $this->primaryResolver
            ->expects($this->once())
            ->method('store')
            ->with($expectedBinary, $expectedPath, $expectedFilter);

        $this->resolver->store($expectedBinary, $expectedPath, $expectedLoader, $expectedFilter);
    }

    /**
     * @test
     */
    public function testProxyCallOnRemove()
    {
        $expectedPaths = ['thePath'];
        $expectedLoaders = ['loader'];
        $expectedFilters = ['theFilter'];

        $this->primaryResolver
            ->expects($this->once())
            ->method('remove')
            ->with($expectedPaths, $expectedFilters);

        $this->resolver->remove($expectedPaths, $expectedLoaders, $expectedFilters);
    }
}
