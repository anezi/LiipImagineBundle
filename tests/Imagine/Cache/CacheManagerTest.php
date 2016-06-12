<?php

namespace Anezi\ImagineBundle\Tests\Imagine\Cache;

use Anezi\ImagineBundle\Imagine\Cache\CacheManager;
use Anezi\ImagineBundle\Model\Binary;
use Anezi\ImagineBundle\Tests\AbstractTest;
use Anezi\ImagineBundle\Imagine\Cache\Signer;
use Anezi\ImagineBundle\ImagineEvents;
use Anezi\ImagineBundle\Events\CacheResolveEvent;
use Anezi\ImagineBundle\Tests\Fixtures\CacheManagerAwareResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @covers Anezi\ImagineBundle\Imagine\Cache\CacheManager
 */
class CacheManagerTest extends AbstractTest
{
    /**
     * @test
     */
    public function testAddCacheManagerAwareResolver()
    {
        $cacheManager = new CacheManager($this->createFilterConfigurationMock(), $this->createRouterMock(), new Signer('secret'), $this->createEventDispatcherMock());

        /** @var \PHPUnit_Framework_MockObject_MockObject|CacheManagerAwareResolver $resolver */
        $resolver = $this->getMock('Anezi\ImagineBundle\Tests\Fixtures\CacheManagerAwareResolver');
        $resolver
            ->expects($this->once())
            ->method('setCacheManager')
            ->with($cacheManager)
        ;

        $cacheManager->addResolver('thumbnail', $resolver);
    }

    /**
     * @test
     */
    public function testGetBrowserPathWithoutResolver()
    {
        $config = $this->createFilterConfigurationMock();
        $config
            ->expects($this->once())
            ->method('get')
            ->with('thumbnail')
            ->will($this->returnValue([
                'size' => [180, 180],
                'mode' => 'outbound',
                'cache' => null,
            ]))
        ;

        $cacheManager = new CacheManager($config, $this->createRouterMock(), new Signer('secret'), $this->createEventDispatcherMock());

        $this->setExpectedException('OutOfBoundsException', 'Could not find resolver "default" for "thumbnail" filter type');
        $cacheManager->getBrowserPath('cats.jpeg', 'thumbnail');
    }

    /**
     * @test
     */
    public function testGetRuntimePath()
    {
        $config = $this->createFilterConfigurationMock();
        $cacheManager = new CacheManager($config, $this->createRouterMock(), new Signer('secret'), $this->createEventDispatcherMock());

        $rcPath = $cacheManager->getRuntimePath('image.jpg', [
            'thumbnail' => [
                'size' => [180, 180],
            ],
        ]);

        $this->assertEquals('rc/ILfTutxX/image.jpg', $rcPath);
    }

    /**
     * @test
     */
    public function testDefaultResolverUsedIfNoneSetOnGetBrowserPath()
    {
        $resolver = $this->createResolverMock();
        $resolver
            ->expects($this->once())
            ->method('isStored')
            ->with('cats.jpeg', 'thumbnail')
            ->will($this->returnValue(true))
        ;
        $resolver
            ->expects($this->once())
            ->method('resolve')
            ->with('cats.jpeg', 'thumbnail')
            ->will($this->returnValue('http://a/path/to/an/image.png'))
        ;

        $config = $this->createFilterConfigurationMock();
        $config
            ->expects($this->exactly(2))
            ->method('get')
            ->with('thumbnail')
            ->will($this->returnValue([
                'size' => [180, 180],
                'mode' => 'outbound',
                'cache' => null,
            ]))
        ;

        $router = $this->createRouterMock();
        $router
            ->expects($this->never())
            ->method('generate')
        ;

        $cacheManager = new CacheManager($config, $router, new Signer('secret'), $this->createEventDispatcherMock());
        $cacheManager->addResolver('default', $resolver);

        $actualBrowserPath = $cacheManager->getBrowserPath('cats.jpeg', 'thumbnail');

        $this->assertEquals('http://a/path/to/an/image.png', $actualBrowserPath);
    }

    /**
     * @test
     */
    public function testFilterActionUrlGeneratedAndReturnIfResolverReturnNullOnGetBrowserPath()
    {
        $resolver = $this->createResolverMock();
        $resolver
            ->expects($this->once())
            ->method('isStored')
            ->with('cats.jpeg', 'thumbnail')
            ->will($this->returnValue(false))
        ;
        $resolver
            ->expects($this->never())
            ->method('resolve')
        ;

        $config = $this->createFilterConfigurationMock();
        $config
            ->expects($this->atLeastOnce())
            ->method('get')
            ->with('thumbnail')
            ->will($this->returnValue([
                'size' => [180, 180],
                'mode' => 'outbound',
                'cache' => null,
            ]))
        ;

        $router = $this->createRouterMock();
        $router
            ->expects($this->once())
            ->method('generate')
            ->will($this->returnValue('/media/cache/thumbnail/cats.jpeg'))
        ;

        $cacheManager = new CacheManager($config, $router, new Signer('secret'), $this->createEventDispatcherMock());
        $cacheManager->addResolver('default', $resolver);

        $actualBrowserPath = $cacheManager->getBrowserPath('cats.jpeg', 'thumbnail');

        $this->assertEquals('/media/cache/thumbnail/cats.jpeg', $actualBrowserPath);
    }

    /**
     * @test
     */
    public function testFilterActionUrlGeneratedAndReturnIfResolverReturnNullOnGetBrowserPathWithRuntimeConfig()
    {
        $runtimeConfig = [
            'thumbnail' => [
                'size' => [100, 100],
            ],
        ];

        $resolver = $this->createResolverMock();
        $resolver
            ->expects($this->once())
            ->method('isStored')
            ->with('rc/VhOzTGRB/cats.jpeg', 'thumbnail')
            ->will($this->returnValue(false))
        ;
        $resolver
            ->expects($this->never())
            ->method('resolve')
        ;

        $config = $this->createFilterConfigurationMock();
        $config
            ->expects($this->atLeastOnce())
            ->method('get')
            ->with('thumbnail')
            ->will($this->returnValue([
                'size' => [180, 180],
                'mode' => 'outbound',
                'cache' => null,
            ]))
        ;

        $router = $this->createRouterMock();
        $router
            ->expects($this->once())
            ->method('generate')
            ->will($this->returnValue('/media/cache/thumbnail/rc/VhOzTGRB/cats.jpeg'))
        ;

        $cacheManager = new CacheManager($config, $router, new Signer('secret'), $this->createEventDispatcherMock());
        $cacheManager->addResolver('default', $resolver);

        $actualBrowserPath = $cacheManager->getBrowserPath('cats.jpeg', 'thumbnail', $runtimeConfig);

        $this->assertEquals('/media/cache/thumbnail/rc/VhOzTGRB/cats.jpeg', $actualBrowserPath);
    }

    /**
     * @dataProvider invalidPathProvider
     */
    public function testResolveInvalidPath($path)
    {
        $cacheManager = new CacheManager(
            $this->createFilterConfigurationMock(),
            $this->createRouterMock(),
            new Signer('secret'),
            $this->createEventDispatcherMock()
        );

        $this->setExpectedException('Symfony\Component\HttpKernel\Exception\NotFoundHttpException');
        $cacheManager->resolve($path, 'thumbnail');
    }

    /**
     * @test
     */
    public function testThrowsIfConcreteResolverNotExists()
    {
        $cacheManager = new CacheManager(
            $this->createFilterConfigurationMock(),
            $this->createRouterMock(),
            new Signer('secret'),
            $this->createEventDispatcherMock()
        );

        $this->setExpectedException('OutOfBoundsException', 'Could not find resolver "default" for "thumbnail" filter type');
        $this->assertFalse($cacheManager->resolve('cats.jpeg', 'thumbnail'));
    }

    /**
     * @test
     */
    public function testFallbackToDefaultResolver()
    {
        $binary = new Binary('aContent', 'image/png', 'png');

        $resolver = $this->createResolverMock();
        $resolver
            ->expects($this->once())
            ->method('resolve')
            ->with('cats.jpeg', 'thumbnail')
            ->will($this->returnValue('/thumbs/cats.jpeg'))
        ;
        $resolver
            ->expects($this->once())
            ->method('store')
            ->with($binary, '/thumbs/cats.jpeg', 'thumbnail')
        ;
        $resolver
            ->expects($this->once())
            ->method('remove')
            ->with(['/thumbs/cats.jpeg'], ['thumbnail'])
            ->will($this->returnValue(true))
        ;

        $config = $this->createFilterConfigurationMock();
        $config
            ->expects($this->exactly(3))
            ->method('get')
            ->with('thumbnail')
            ->will($this->returnValue([
                'size' => [180, 180],
                'mode' => 'outbound',
                'cache' => null,
            ]))
        ;

        $cacheManager = new CacheManager(
            $config,
            $this->createRouterMock(),
            new Signer('secret'),
            $this->createEventDispatcherMock()
        );
        $cacheManager->addResolver('default', $resolver);

        // Resolve fallback to default resolver
        $this->assertEquals('/thumbs/cats.jpeg', $cacheManager->resolve('cats.jpeg', 'thumbnail'));

        $cacheManager->store($binary, '/thumbs/cats.jpeg', 'default', 'thumbnail');

        // Remove fallback to default resolver
        $cacheManager->remove('/thumbs/cats.jpeg', 'thumbnail');
    }

    /**
     * @test
     */
    public function testGenerateUrl()
    {
        $path = 'thePath';
        $expectedUrl = 'theUrl';

        $routerMock = $this->createRouterMock();
        $routerMock
            ->expects($this->once())
            ->method('generate')
            ->with(
                'anezi_imagine_load',
                [
                    'path' => $path,
                    'filter' => 'thumbnail',
                ],
                UrlGeneratorInterface::ABSOLUTE_URL
            )
            ->will($this->returnValue($expectedUrl))
        ;

        $cacheManager = new CacheManager(
            $this->createFilterConfigurationMock(),
            $routerMock,
            new Signer('secret'),
            $this->createEventDispatcherMock()
        );

        $this->assertEquals(
            $expectedUrl,
            $cacheManager->generateUrl($path, 'thumbnail')
        );
    }

    /**
     * @test
     */
    public function testRemoveCacheForPathAndFilterOnRemove()
    {
        $expectedPath = 'thePath';
        $expectedFilter = 'theFilter';

        $resolver = $this->createResolverMock();
        $resolver
            ->expects($this->once())
            ->method('remove')
            ->with([$expectedPath], [$expectedFilter])
        ;

        $config = $this->createFilterConfigurationMock();
        $config
            ->expects($this->atLeastOnce())
            ->method('get')
            ->will($this->returnCallback(function ($filter) {
                return [
                    'cache' => $filter,
                ];
            }))
        ;

        $cacheManager = new CacheManager($config, $this->createRouterMock(), new Signer('secret'), $this->createEventDispatcherMock());
        $cacheManager->addResolver($expectedFilter, $resolver);

        $cacheManager->remove($expectedPath, $expectedFilter);
    }

    /**
     * @test
     */
    public function testRemoveCacheForPathAndSomeFiltersOnRemove()
    {
        $expectedPath = 'thePath';
        $expectedFilterOne = 'theFilterOne';
        $expectedFilterTwo = 'theFilterTwo';

        $resolverOne = $this->createResolverMock();
        $resolverOne
            ->expects($this->once())
            ->method('remove')
            ->with([$expectedPath], [$expectedFilterOne])
        ;

        $resolverTwo = $this->createResolverMock();
        $resolverTwo
            ->expects($this->once())
            ->method('remove')
            ->with([$expectedPath], [$expectedFilterTwo])
        ;

        $config = $this->createFilterConfigurationMock();
        $config
            ->expects($this->atLeastOnce())
            ->method('get')
            ->will($this->returnCallback(function ($filter) {
                return [
                    'cache' => $filter,
                ];
            }))
        ;

        $cacheManager = new CacheManager($config, $this->createRouterMock(), new Signer('secret'), $this->createEventDispatcherMock());
        $cacheManager->addResolver($expectedFilterOne, $resolverOne);
        $cacheManager->addResolver($expectedFilterTwo, $resolverTwo);

        $cacheManager->remove($expectedPath, [$expectedFilterOne, $expectedFilterTwo]);
    }

    /**
     * @test
     */
    public function testRemoveCacheForSomePathsAndFilterOnRemove()
    {
        $expectedPathOne = 'thePathOne';
        $expectedPathTwo = 'thePathTwo';
        $expectedFilter = 'theFilter';

        $resolver = $this->createResolverMock();
        $resolver
            ->expects($this->once())
            ->method('remove')
            ->with(
                [$expectedPathOne, $expectedPathTwo],
                [$expectedFilter]
            )
        ;

        $config = $this->createFilterConfigurationMock();
        $config
            ->expects($this->atLeastOnce())
            ->method('get')
            ->will($this->returnCallback(function ($filter) {
                return [
                    'cache' => $filter,
                ];
            }))
        ;

        $cacheManager = new CacheManager($config, $this->createRouterMock(), new Signer('secret'), $this->createEventDispatcherMock());
        $cacheManager->addResolver($expectedFilter, $resolver);

        $cacheManager->remove([$expectedPathOne, $expectedPathTwo], $expectedFilter);
    }

    /**
     * @test
     */
    public function testRemoveCacheForSomePathsAndSomeFiltersOnRemove()
    {
        $expectedPathOne = 'thePath';
        $expectedPathTwo = 'thePath';
        $expectedFilterOne = 'theFilterOne';
        $expectedFilterTwo = 'theFilterTwo';

        $resolverOne = $this->createResolverMock();
        $resolverOne
            ->expects($this->once())
            ->method('remove')
            ->with([$expectedPathOne, $expectedPathTwo], [$expectedFilterOne])
        ;

        $resolverTwo = $this->createResolverMock();
        $resolverTwo
            ->expects($this->once())
            ->method('remove')
            ->with([$expectedPathOne, $expectedPathTwo], [$expectedFilterTwo])
        ;

        $config = $this->createFilterConfigurationMock();
        $config
            ->expects($this->atLeastOnce())
            ->method('get')
            ->will($this->returnCallback(function ($filter) {
                return [
                    'cache' => $filter,
                ];
            }))
        ;

        $cacheManager = new CacheManager($config, $this->createRouterMock(), new Signer('secret'), $this->createEventDispatcherMock());
        $cacheManager->addResolver($expectedFilterOne, $resolverOne);
        $cacheManager->addResolver($expectedFilterTwo, $resolverTwo);

        $cacheManager->remove(
            [$expectedPathOne, $expectedPathTwo],
            [$expectedFilterOne, $expectedFilterTwo]
        );
    }

    /**
     * @test
     */
    public function testRemoveCacheForAllFiltersOnRemove()
    {
        $expectedFilterOne = 'theFilterOne';
        $expectedFilterTwo = 'theFilterTwo';

        $resolverOne = $this->createResolverMock();
        $resolverOne
            ->expects($this->once())
            ->method('remove')
            ->with([], [$expectedFilterOne])
        ;

        $resolverTwo = $this->createResolverMock();
        $resolverTwo
            ->expects($this->once())
            ->method('remove')
            ->with([], [$expectedFilterTwo])
        ;

        $config = $this->createFilterConfigurationMock();
        $config
            ->expects($this->atLeastOnce())
            ->method('get')
            ->will($this->returnCallback(function ($filter) {
                return [
                    'cache' => $filter,
                ];
            }))
        ;
        $config
            ->expects($this->once())
            ->method('all')
            ->will($this->returnValue([
                $expectedFilterOne => [],
                $expectedFilterTwo => [],
            ]))
        ;

        $cacheManager = new CacheManager($config, $this->createRouterMock(), new Signer('secret'), $this->createEventDispatcherMock());
        $cacheManager->addResolver($expectedFilterOne, $resolverOne);
        $cacheManager->addResolver($expectedFilterTwo, $resolverTwo);

        $cacheManager->remove();
    }

    /**
     * @test
     */
    public function testRemoveCacheForPathAndAllFiltersOnRemove()
    {
        $expectedFilterOne = 'theFilterOne';
        $expectedFilterTwo = 'theFilterTwo';
        $expectedPath = 'thePath';

        $resolverOne = $this->createResolverMock();
        $resolverOne
            ->expects($this->once())
            ->method('remove')
            ->with([$expectedPath], [$expectedFilterOne])
        ;

        $resolverTwo = $this->createResolverMock();
        $resolverTwo
            ->expects($this->once())
            ->method('remove')
            ->with([$expectedPath], [$expectedFilterTwo])
        ;

        $config = $this->createFilterConfigurationMock();
        $config
            ->expects($this->atLeastOnce())
            ->method('get')
            ->will($this->returnCallback(function ($filter) {
                return [
                    'cache' => $filter,
                ];
            }))
        ;
        $config
            ->expects($this->once())
            ->method('all')
            ->will($this->returnValue([
                $expectedFilterOne => [],
                $expectedFilterTwo => [],
            ]))
        ;

        $cacheManager = new CacheManager($config, $this->createRouterMock(), new Signer('secret'), $this->createEventDispatcherMock());
        $cacheManager->addResolver($expectedFilterOne, $resolverOne);
        $cacheManager->addResolver($expectedFilterTwo, $resolverTwo);

        $cacheManager->remove($expectedPath);
    }

    /**
     * @test
     */
    public function testAggregateFiltersByResolverOnRemove()
    {
        $expectedFilterOne = 'theFilterOne';
        $expectedFilterTwo = 'theFilterTwo';

        $resolver = $this->createResolverMock();
        $resolver
            ->expects($this->once())
            ->method('remove')
            ->with([], [$expectedFilterOne, $expectedFilterTwo])
        ;

        $config = $this->createFilterConfigurationMock();
        $config
            ->expects($this->atLeastOnce())
            ->method('get')
            ->will($this->returnCallback(function ($filter) {
                return [
                    'cache' => $filter,
                ];
            }))
        ;

        $cacheManager = new CacheManager($config, $this->createRouterMock(), new Signer('secret'), $this->createEventDispatcherMock());
        $cacheManager->addResolver($expectedFilterOne, $resolver);
        $cacheManager->addResolver($expectedFilterTwo, $resolver);

        $cacheManager->remove(null, [$expectedFilterOne, $expectedFilterTwo]);
    }

    /**
     * @test
     */
    public function testShouldDispatchCachePreResolveEvent()
    {
        $dispatcher = $this->createEventDispatcherMock();
        $dispatcher
            ->expects($this->at(0))
            ->method('dispatch')
            ->with(ImagineEvents::PRE_RESOLVE, new CacheResolveEvent('cats.jpg', 'thumbnail'))
        ;

        $cacheManager = new CacheManager(
            $this->createFilterConfigurationMock(),
            $this->createRouterMock(),
            new Signer('secret'),
            $dispatcher
        );
        $cacheManager->addResolver('default', $this->createResolverMock());

        $cacheManager->resolve('cats.jpg', 'thumbnail');
    }

    /**
     * @test
     */
    public function testShouldDispatchCachePostResolveEvent()
    {
        $dispatcher = $this->createEventDispatcherMock();
        $dispatcher
            ->expects($this->at(1))
            ->method('dispatch')
            ->with(ImagineEvents::POST_RESOLVE, new CacheResolveEvent('cats.jpg', 'thumbnail'))
        ;

        $cacheManager = new CacheManager(
            $this->createFilterConfigurationMock(),
            $this->createRouterMock(),
            new Signer('secret'),
            $dispatcher
        );
        $cacheManager->addResolver('default', $this->createResolverMock());

        $cacheManager->resolve('cats.jpg', 'thumbnail');
    }

    /**
     * @test
     */
    public function testShouldAllowToPassChangedDataFromPreResolveEventToResolver()
    {
        $dispatcher = $this->createEventDispatcherMock();
        $dispatcher
            ->expects($this->at(0))
            ->method('dispatch')
            ->with(ImagineEvents::PRE_RESOLVE, $this->isInstanceOf('Anezi\ImagineBundle\Events\CacheResolveEvent'))
            ->will($this->returnCallback(function ($name, $event) {
                $event->setPath('changed_path');
                $event->setFilter('changed_filter');
            }))
        ;

        $resolver = $this->createResolverMock();
        $resolver
            ->expects($this->once())
            ->method('resolve')
            ->with('changed_path', 'changed_filter')
        ;

        $cacheManager = new CacheManager(
            $this->createFilterConfigurationMock(),
            $this->createRouterMock(),
            new Signer('secret'),
            $dispatcher
        );
        $cacheManager->addResolver('default', $resolver);

        $cacheManager->resolve('cats.jpg', 'thumbnail');
    }

    /**
     * @test
     */
    public function testShouldAllowToGetResolverByFilterChangedInPreResolveEvent()
    {
        $dispatcher = $this->createEventDispatcherMock();
        $dispatcher
            ->expects($this->at(0))
            ->method('dispatch')
            ->will($this->returnCallback(function ($name, $event) {
                $event->setFilter('thumbnail');
            }))
        ;

        /** @var CacheManager|\PHPUnit_Framework_MockObject_MockObject $cacheManager */
        $cacheManager = $this->getMock('Anezi\ImagineBundle\Imagine\Cache\CacheManager', ['getResolver'], array(
            $this->createFilterConfigurationMock(),
            $this->createRouterMock(),
            new Signer('secret'),
            $dispatcher,
        ));

        $cacheManager
            ->expects($this->once())
            ->method('getResolver')
            ->with('thumbnail')
            ->will($this->returnValue($this->createResolverMock()))
        ;

        $cacheManager->resolve('cats.jpg', 'default');
    }

    /**
     * @test
     */
    public function testShouldAllowToPassChangedDataFromPreResolveEventToPostResolveEvent()
    {
        $dispatcher = $this->createEventDispatcherMock();
        $dispatcher
            ->expects($this->at(0))
            ->method('dispatch')
            ->with(ImagineEvents::PRE_RESOLVE, $this->isInstanceOf('Anezi\ImagineBundle\Events\CacheResolveEvent'))
            ->will($this->returnCallback(function ($name, $event) {
                $event->setPath('changed_path');
                $event->setFilter('changed_filter');
            }))
        ;

        $dispatcher
            ->expects($this->at(1))
            ->method('dispatch')
            ->with(
                ImagineEvents::POST_RESOLVE,
                $this->logicalAnd(
                    $this->isInstanceOf('Anezi\ImagineBundle\Events\CacheResolveEvent'),
                    $this->attributeEqualTo('path', 'changed_path'),
                    $this->attributeEqualTo('filter', 'changed_filter')
            ))
        ;

        $cacheManager = new CacheManager(
            $this->createFilterConfigurationMock(),
            $this->createRouterMock(),
            new Signer('secret'),
            $dispatcher
        );
        $cacheManager->addResolver('default', $this->createResolverMock());

        $cacheManager->resolve('cats.jpg', 'thumbnail');
    }

    /**
     * @test
     */
    public function testShouldReturnUrlChangedInPostResolveEvent()
    {
        $dispatcher = $this->createEventDispatcherMock();
        $dispatcher
            ->expects($this->at(1))
            ->method('dispatch')
            ->with(ImagineEvents::POST_RESOLVE, $this->isInstanceOf('Anezi\ImagineBundle\Events\CacheResolveEvent'))
            ->will($this->returnCallback(function ($name, $event) {
                $event->setUrl('changed_url');
            }))
        ;

        $cacheManager = new CacheManager(
            $this->createFilterConfigurationMock(),
            $this->createRouterMock(),
            new Signer('secret'),
            $dispatcher
        );
        $cacheManager->addResolver('default', $this->createResolverMock());

        $url = $cacheManager->resolve('cats.jpg', 'thumbnail');

        $this->assertEquals('changed_url', $url);
    }
}
