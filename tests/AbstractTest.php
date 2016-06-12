<?php

namespace Anezi\ImagineBundle\tests;

use Anezi\ImagineBundle\Imagine\Cache\CacheManager;
use Anezi\ImagineBundle\Imagine\Cache\Resolver\ResolverInterface;
use Anezi\ImagineBundle\Imagine\Filter\FilterConfiguration;
use Anezi\ImagineBundle\Imagine\Filter\FilterManager;
use Imagine\Image\ImageInterface;
use Imagine\Image\ImagineInterface;
use Imagine\Image\Metadata\MetadataBag;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Routing\RouterInterface;

/**
 * Class AbstractTest.
 */
abstract class AbstractTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var string
     */
    protected $fixturesDir;

    /**
     * @var string
     */
    protected $tempDir;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->fixturesDir = __DIR__.'/Fixtures';

        $this->tempDir = str_replace('/', DIRECTORY_SEPARATOR, sys_get_temp_dir().'/anezi_imagine_test');

        $this->filesystem = new Filesystem();

        if ($this->filesystem->exists($this->tempDir)) {
            $this->filesystem->remove($this->tempDir);
        }

        $this->filesystem->mkdir($this->tempDir);
    }

    /**
     * @return array
     */
    public function invalidPathProvider()
    {
        return [
            [$this->fixturesDir.'/assets/../../foobar.png'],
            [$this->fixturesDir.'/assets/some_folder/../foobar.png'],
            ['../../outside/foobar.jpg'],
        ];
    }

    /**
     * @return FilterConfiguration
     */
    protected function createFilterConfiguration()
    {
        $config = new FilterConfiguration();
        $config->set('thumbnail', [
            'size' => [180, 180],
            'mode' => 'outbound',
        ]);

        return $config;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|CacheManager
     */
    protected function getMockCacheManager()
    {
        return $this->getMock('Anezi\ImagineBundle\Imagine\Cache\CacheManager', [], [], '', false);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|FilterManager
     */
    protected function createFilterManagerMock()
    {
        return $this->getMock('Anezi\ImagineBundle\Imagine\Filter\FilterManager', [], [], '', false);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|FilterConfiguration
     */
    protected function createFilterConfigurationMock()
    {
        return $this->getMock('Anezi\ImagineBundle\Imagine\Filter\FilterConfiguration');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|RouterInterface
     */
    protected function createRouterMock()
    {
        return $this->getMock('Symfony\Component\Routing\RouterInterface');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|ResolverInterface
     */
    protected function createResolverMock()
    {
        return $this->getMock('Anezi\ImagineBundle\Imagine\Cache\Resolver\ResolverInterface');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|EventDispatcherInterface
     */
    protected function createEventDispatcherMock()
    {
        return $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|ImageInterface
     */
    protected function getMockImage()
    {
        return $this->getMock('Imagine\Image\ImageInterface');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|MetadataBag
     */
    protected function getMockMetaData()
    {
        return $this->getMock('Imagine\Image\Metadata\MetadataBag');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|ImagineInterface
     */
    protected function createImagineMock()
    {
        return $this->getMock('Imagine\Image\ImagineInterface');
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        if (!$this->filesystem) {
            return;
        }

        if ($this->filesystem->exists($this->tempDir)) {
            $this->filesystem->remove($this->tempDir);
        }
    }
}
