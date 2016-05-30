<?php

namespace Anezi\ImagineBundle\Tests\Exception\Imagine\Cache\Resolver;

use Anezi\ImagineBundle\Exception\Imagine\Cache\Resolver\NotResolvableException;

/**
 * @covers Anezi\ImagineBundle\Exception\Imagine\Cache\Resolver\NotResolvableException
 */
class NotResolvableExceptionTest extends \PHPUnit_Framework_TestCase
{
    public function testSubClassOfRuntimeException()
    {
        $e = new NotResolvableException();

        $this->assertInstanceOf('\RuntimeException', $e);
    }

    public function testImplementsExceptionInterface()
    {
        $e = new NotResolvableException();

        $this->assertInstanceOf('Anezi\ImagineBundle\Exception\ExceptionInterface', $e);
    }
}
