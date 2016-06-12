<?php

namespace Anezi\ImagineBundle\tests\Controller;

use Anezi\ImagineBundle\Controller\ImagineController;
use Anezi\ImagineBundle\Imagine\Cache\CacheManager;
use Anezi\ImagineBundle\Imagine\Data\DataManager;
use Anezi\ImagineBundle\Imagine\Filter\FilterManager;

/**
 * @covers Anezi\ImagineBundle\Controller\ImagineController
 */
class ImagineControllerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function testCouldBeConstructedWithExpectedServices()
    {
        new ImagineController(
            $this->createDataManagerMock(),
            $this->createFilterManagerMock(),
            $this->createCacheManagerMock(),
            $this->createSignerMock(),
            $this->createLoggerMock()
        );
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|DataManager
     */
    protected function createDataManagerMock()
    {
        return $this->getMock('Anezi\ImagineBundle\Imagine\Data\DataManager', [], [], '', false);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|FilterManager
     */
    protected function createFilterManagerMock()
    {
        return $this->getMock('Anezi\ImagineBundle\Imagine\Filter\FilterManager', [], [], '', false);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|CacheManager
     */
    protected function createCacheManagerMock()
    {
        return $this->getMock('Anezi\ImagineBundle\Imagine\Cache\CacheManager', [], [], '', false);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Anezi\ImagineBundle\Imagine\Cache\SignerInterface
     */
    protected function createSignerMock()
    {
        return $this->getMock('Anezi\ImagineBundle\Imagine\Cache\Signer', [], [], '', false);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Psr\Log\LoggerInterface
     */
    protected function createLoggerMock()
    {
        return $this->getMock('Psr\Log\LoggerInterface');
    }
}
