<?php

namespace Anezi\ImagineBundle\tests\Binary\Loader;

use Anezi\ImagineBundle\Binary\Loader\AbstractDoctrineLoader;
use Doctrine\Common\Persistence\ObjectRepository;

/**
 * @covers Anezi\ImagineBundle\Binary\Loader\AbstractDoctrineLoader<extended>
 */
class AbstractDoctrineLoaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectRepository
     */
    private $om;

    /**
     * @var AbstractDoctrineLoader
     */
    private $loader;

    public function setUp()
    {
        $this->om = $this->getMock('Doctrine\Common\Persistence\ObjectManager');

        $this->loader = $this->getMockBuilder('Anezi\ImagineBundle\Binary\Loader\AbstractDoctrineLoader')->setConstructorArgs([$this->om])->getMockForAbstractClass();
    }

    public function testFindWithValidObjectFirstHit()
    {
        $image = new \stdClass();

        $this->loader->expects($this->atLeastOnce())->method('mapPathToId')->with('/foo/bar')->will($this->returnValue(1337));
        $this->loader->expects($this->atLeastOnce())->method('getStreamFromImage')->with($image)->will($this->returnValue(fopen('data://text/plain,foo', 'r')));

        $this->om->expects($this->atLeastOnce())->method('find')->with(null, 1337)->will($this->returnValue($image));

        $this->assertSame('foo', $this->loader->find('/foo/bar'));
    }

    public function testFindWithValidObjectSecondHit()
    {
        $image = new \stdClass();

        $this->loader->expects($this->atLeastOnce())->method('mapPathToId')->will($this->returnValueMap([
            ['/foo/bar.png', 1337],
            ['/foo/bar', 4711],
        ]));

        $this->loader->expects($this->atLeastOnce())->method('getStreamFromImage')->with($image)->will($this->returnValue(fopen('data://text/plain,foo', 'r')));

        $this->om->expects($this->atLeastOnce())->method('find')->will($this->returnValueMap([
            [null, 1337, null],
            [null, 4711, $image],
        ]));

        $this->assertSame('foo', $this->loader->find('/foo/bar.png'));
    }

    /**
     * @expectedException \Anezi\ImagineBundle\Exception\Binary\Loader\NotLoadableException
     */
    public function testFindWithInvalidObject()
    {
        $this->loader->expects($this->atLeastOnce())->method('mapPathToId')->with('/foo/bar')->will($this->returnValue(1337));
        $this->loader->expects($this->never())->method('getStreamFromImage');

        $this->om->expects($this->atLeastOnce())->method('find')->with(null, 1337)->will($this->returnValue(null));

        $this->loader->find('/foo/bar');
    }
}
