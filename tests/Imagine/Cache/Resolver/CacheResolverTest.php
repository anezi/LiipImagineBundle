<?php

namespace Anezi\ImagineBundle\tests\Imagine\Cache\Resolver;

use Anezi\ImagineBundle\Imagine\Cache\Resolver\CacheResolver;
use Anezi\ImagineBundle\Model\Binary;
use Anezi\ImagineBundle\Tests\AbstractTest;
use Doctrine\Common\Cache\ArrayCache;

/**
 * @covers Anezi\ImagineBundle\Imagine\Cache\Resolver\CacheResolver
 */
class CacheResolverTest extends AbstractTest
{
    /**
     * @var string
     */
    protected $loader = 'loader';
    /**
     * @var string
     */
    protected $filter = 'thumbnail';

    /**
     * @var string
     */
    protected $path = 'MadCat2.jpeg';

    /**
     * @var string
     */
    protected $webPath = '/media/cache/thumbnail/MadCat2.jpeg';

    /**
     * @test
     */
    public function testResolveIsSavedToCache()
    {
        $resolver = $this->createResolverMock();
        $resolver
            ->expects($this->once())
            ->method('resolve')
            ->with($this->path, $this->filter)
            ->will($this->returnValue($this->webPath));

        $cacheResolver = new CacheResolver(new ArrayCache(), $resolver);

        $this->assertSame($this->webPath, $cacheResolver->resolve($this->path, $this->loader, $this->filter));

        // Call multiple times to verify the cache is used.
        $this->assertSame($this->webPath, $cacheResolver->resolve($this->path, $this->loader, $this->filter));
        $this->assertSame($this->webPath, $cacheResolver->resolve($this->path, $this->loader, $this->filter));
    }

    /**
     * @test
     */
    public function testNotCallInternalResolverIfCachedOnIsStored()
    {
        $resolver = $this->createResolverMock();
        $resolver
            ->expects($this->once())
            ->method('resolve')
            ->with($this->path, $this->filter)
            ->will($this->returnValue($this->webPath));
        $resolver
            ->expects($this->never())
            ->method('isStored');

        $cacheResolver = new CacheResolver(new ArrayCache(), $resolver);

        $cacheResolver->resolve($this->path, $this->loader, $this->filter);

        // Call multiple times to verify the cache is used.
        $this->assertTrue($cacheResolver->isStored($this->path, $this->loader, $this->filter));
        $this->assertTrue($cacheResolver->isStored($this->path, $this->loader, $this->filter));
    }

    /**
     * @test
     */
    public function testCallInternalResolverIfNotCachedOnIsStored()
    {
        $resolver = $this->createResolverMock();
        $resolver
            ->expects($this->exactly(2))
            ->method('isStored')
            ->will($this->returnValue(true));

        $cacheResolver = new CacheResolver(new ArrayCache(), $resolver);

        $this->assertTrue($cacheResolver->isStored($this->path, $this->loader, $this->filter));
        $this->assertTrue($cacheResolver->isStored($this->path, $this->loader, $this->filter));
    }

    /**
     * @test
     */
    public function testStoreIsForwardedToResolver()
    {
        $binary = new Binary('aContent', 'image/jpeg', 'jpg');

        $resolver = $this->createResolverMock();
        $resolver
            ->expects($this->exactly(2))
            ->method('store')
            ->with($this->identicalTo($binary), $this->webPath, $this->filter);

        $cacheResolver = new CacheResolver(new ArrayCache(), $resolver);

        // Call twice, as this method should not be cached.
        $this->assertNull($cacheResolver->store($binary, $this->webPath, $this->loader, $this->filter));
        $this->assertNull($cacheResolver->store($binary, $this->webPath, $this->loader, $this->filter));
    }

    /**
     * @test
     */
    public function testSavesToCacheIfInternalResolverReturnUrlOnResolve()
    {
        $resolver = $this->createResolverMock();
        $resolver
            ->expects($this->once())
            ->method('resolve')
            ->with($this->path, $this->filter)
            ->will($this->returnValue('/the/expected/browser'));

        $cache = $this->getMock('Doctrine\Common\Cache\Cache');
        $cache
            ->expects($this->exactly(1))
            ->method('save');

        $cacheResolver = new CacheResolver($cache, $resolver);

        $cacheResolver->resolve($this->path, $this->loader, $this->filter);
    }

    /**
     * @test
     */
    public function testRemoveSinglePathCacheOnRemove()
    {
        $resolver = $this->createResolverMock();
        $resolver
            ->expects($this->once())
            ->method('resolve')
            ->with($this->path, $this->filter)
            ->will($this->returnValue($this->webPath));
        $resolver
            ->expects($this->once())
            ->method('remove');

        $cache = new ArrayCache();

        $cacheResolver = new CacheResolver($cache, $resolver);
        $cacheResolver->resolve($this->path, $this->loader, $this->filter);

        /*
         * Checking 2 items:
         * * The result of one resolve execution.
         * * The index of entity.
         */
        $this->assertCount(2, $this->getCacheEntries($cache));

        $cacheResolver->remove([$this->path], [$this->loader], [$this->filter]);

        // Cache including index has been removed.
        $this->assertCount(0, $this->getCacheEntries($cache));
    }

    /**
     * @test
     */
    public function testRemoveAllFilterCacheOnRemove()
    {
        $resolver = $this->createResolverMock();
        $resolver
            ->expects($this->exactly(4))
            ->method('resolve')
            ->will($this->returnValue('aCachePath'));
        $resolver
            ->expects($this->once())
            ->method('remove');

        $cache = new ArrayCache();

        $cacheResolver = new CacheResolver($cache, $resolver);
        $cacheResolver->resolve('aPathFoo', $this->loader, 'thumbnail_233x233');
        $cacheResolver->resolve('aPathBar', $this->loader, 'thumbnail_233x233');
        $cacheResolver->resolve('aPathFoo', $this->loader, 'thumbnail_100x100');
        $cacheResolver->resolve('aPathBar', $this->loader, 'thumbnail_100x100');

        /*
         * Checking 6 items:
         * * The result of four resolve execution.
         * * The index of two entities.
         */
        $this->assertCount(6, $this->getCacheEntries($cache));

        $cacheResolver->remove([], [$this->loader], ['thumbnail_233x233']);

        // Cache including index has been removed.
        $this->assertCount(3, $this->getCacheEntries($cache));
    }

    /**
     * There's an intermittent cache entry which is a cache namespace
     * version, it may or may not be there depending on doctrine-cache
     * version. There's no point in checking it anyway since it's a detail
     * of doctrine cache implementation.
     *
     * @param ArrayCache $cache
     *
     * @return array
     */
    private function getCacheEntries(ArrayCache $cache)
    {
        $cacheEntries = $this->readAttribute($cache, 'data');
        unset($cacheEntries['DoctrineNamespaceCacheKey[]']);

        return $cacheEntries;
    }
}
