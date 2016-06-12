<?php

namespace Anezi\ImagineBundle\tests\Twig\Helper;

use Anezi\ImagineBundle\Imagine\Cache\CacheManager;
use Anezi\ImagineBundle\Twig\Helper\ImagineHelper;

/**
 * @covers Anezi\ImagineBundle\Twig\Helper\ImagineHelper
 */
class ImagineHelperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function testSubClassOfHelper()
    {
        $rc = new \ReflectionClass('Anezi\ImagineBundle\Twig\Helper\ImagineHelper');

        $this->assertTrue($rc->isSubclassOf('Symfony\Component\Twig\Helper\Helper'));
    }

    /**
     * @test
     */
    public function testCouldBeConstructedWithCacheManagerAsArgument()
    {
        new ImagineHelper($this->createCacheManagerMock());
    }

    /**
     * @test
     */
    public function testAllowGetName()
    {
        $helper = new ImagineHelper($this->createCacheManagerMock());

        $this->assertSame('anezi_imagine', $helper->getName());
    }

    /**
     * @test
     */
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
            ->will($this->returnValue($expectedCachePath));

        $helper = new ImagineHelper($cacheManager);

        $this->assertSame($expectedCachePath, $helper->filter($expectedPath, $expectedFilter));
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|CacheManager
     */
    protected function createCacheManagerMock()
    {
        return $this->getMock('Anezi\ImagineBundle\Imagine\Cache\CacheManager', [], [], '', false);
    }
}
