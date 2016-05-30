<?php

namespace Anezi\ImagineBundle\Tests\Imagine\Cache;

use Anezi\ImagineBundle\Tests\AbstractTest;
use Anezi\ImagineBundle\Imagine\Cache\Signer;

class SignerTest extends AbstractTest
{
    public function testImplementsSignerInterface()
    {
        $rc = new \ReflectionClass('Anezi\ImagineBundle\Imagine\Cache\Signer');

        $this->assertTrue($rc->implementsInterface('Anezi\ImagineBundle\Imagine\Cache\SignerInterface'));
    }

    public function testCouldBeConstructedWithSecret()
    {
        new Signer('aSecret');
    }

    public function testShouldReturnShortHashOnSign()
    {
        $singer = new Signer('aSecret');

        $this->assertEquals(8, strlen($singer->sign('aPath')));
    }

    public function testShouldSingAndSuccessfullyCheckPathWithoutRuntimeConfig()
    {
        $singer = new Signer('aSecret');

        $this->assertTrue($singer->check($singer->sign('aPath'), 'aPath'));
    }

    public function testShouldSingAndSuccessfullyCheckPathWithRuntimeConfig()
    {
        $singer = new Signer('aSecret');

        $this->assertTrue($singer->check($singer->sign('aPath', array('aConfig')), 'aPath', array('aConfig')));
    }

    public function testShouldConvertRecursivelyToStringAllRuntimeConfigParameters()
    {
        $singer = new Signer('aSecret');

        $runtimeConfigInts = array(
            'foo' => 14,
            'bar' => array(
                'bar' => 15,
            ),
        );

        $runtimeConfigStrings = array(
            'foo' => '14',
            'bar' => array(
                'bar' => '15',
            ),
        );

        $this->assertTrue($singer->check($singer->sign('aPath', $runtimeConfigInts), 'aPath', $runtimeConfigStrings));
    }
}
