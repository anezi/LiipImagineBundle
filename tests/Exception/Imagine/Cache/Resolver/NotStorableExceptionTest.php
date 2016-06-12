<?php

namespace Anezi\ImagineBundle\tests\Exception\Imagine\Cache\Resolver;

use Anezi\ImagineBundle\Exception\Imagine\Cache\Resolver\NotStorableException;

/**
 * @covers Anezi\ImagineBundle\Exception\Imagine\Cache\Resolver\NotStorableException
 */
class NotStorableExceptionTest extends \PHPUnit_Framework_TestCase
{
    public function testSubClassOfRuntimeException()
    {
        $e = new NotStorableException();

        $this->assertInstanceOf('\RuntimeException', $e);
    }

    public function testImplementsExceptionInterface()
    {
        $e = new NotStorableException();

        $this->assertInstanceOf('Anezi\ImagineBundle\Exception\ExceptionInterface', $e);
    }
}
