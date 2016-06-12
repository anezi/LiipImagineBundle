<?php

namespace Anezi\ImagineBundle\Tests\Twig\Helper;

use Anezi\ImagineBundle\Imagine\Cache\CacheManager;
use Anezi\ImagineBundle\Twig\ImagineExtension;

/**
 * @covers Anezi\ImagineBundle\Twig\ImagineExtension
 */
class ImagineExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function testSubClassOfHelper()
    {
        $rc = new \ReflectionClass('Anezi\ImagineBundle\Twig\ImagineExtension');

        $this->assertTrue($rc->isSubclassOf('Twig_Extension'));
    }

    /**
     * @test
     */
    public function testCouldBeConstructedWithCacheManagerAsArgument()
    {
        new ImagineExtension($this->createCacheManagerMock());
    }

    /**
     * @test
     */
    public function testAllowGetName()
    {
        $extension = new ImagineExtension($this->createCacheManagerMock());

        $this->assertSame('anezi_imagine', $extension->getName());
    }

    /**
     * @test
     */
    public function testProxyCallToCacheManagerOnFilter()
    {
        $expectedPath = 'thePathToTheImage';
        $expectedLoader = 'loader';
        $expectedFilter = 'thumbnail';
        $expectedCachePath = 'thePathToTheCachedImage';

        $cacheManager = $this->createCacheManagerMock();
        $cacheManager
            ->expects($this->once())
            ->method('getBrowserPath')
            ->with($expectedPath, $expectedFilter)
            ->will($this->returnValue($expectedCachePath));

        $extension = new ImagineExtension($cacheManager);

        $this->assertSame($expectedCachePath, $extension->filter($expectedPath, $expectedLoader, $expectedFilter));
    }

    /**
     * @test
     */
    public function testAddsFilterMethodToFiltersList()
    {
        $extension = new ImagineExtension($this->createCacheManagerMock());

        $filters = $extension->getFilters();

        $this->assertInternalType('array', $filters);
        $this->assertCount(1, $filters);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|CacheManager
     */
    protected function createCacheManagerMock()
    {
        return $this->getMock('Anezi\ImagineBundle\Imagine\Cache\CacheManager', [], [], '', false);
    }
}
