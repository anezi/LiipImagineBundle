<?php

namespace Anezi\ImagineBundle\Tests\Templating\Helper;

use Anezi\ImagineBundle\Imagine\Cache\CacheManager;
use Anezi\ImagineBundle\Templating\ImagineExtension;

/**
 * @covers Anezi\ImagineBundle\Templating\ImagineExtension
 */
class ImagineExtensionTest extends \PHPUnit_Framework_TestCase
{
    public function testSubClassOfHelper()
    {
        $rc = new \ReflectionClass('Anezi\ImagineBundle\Templating\ImagineExtension');

        $this->assertTrue($rc->isSubclassOf('Twig_Extension'));
    }

    public function testCouldBeConstructedWithCacheManagerAsArgument()
    {
        new ImagineExtension($this->createCacheManagerMock());
    }

    public function testAllowGetName()
    {
        $extension = new ImagineExtension($this->createCacheManagerMock());

        $this->assertEquals('anezi_imagine', $extension->getName());
    }

    public function testProxyCallToCacheManagerOnFilter()
    {
        $expectedPath = 'thePathToTheImage';
        $expectedFilter = 'thumbnail';
        $expectedCachePath = 'thePathToTheCachedImage';

        $cacheManager = $this->createCacheManagerMock();
        $cacheManager
            ->expects($this->once())
            ->method('getBrowserPath')
            ->with($expectedPath, $expectedFilter)
            ->will($this->returnValue($expectedCachePath))
        ;

        $extension = new ImagineExtension($cacheManager);

        $this->assertEquals($expectedCachePath, $extension->filter($expectedPath, $expectedFilter));
    }

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
        return $this->getMock('Anezi\ImagineBundle\Imagine\Cache\CacheManager', array(), array(), '', false);
    }
}
