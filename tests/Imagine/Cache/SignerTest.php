<?php

namespace Anezi\ImagineBundle\tests\Imagine\Cache;

use Anezi\ImagineBundle\Imagine\Cache\Signer;
use Anezi\ImagineBundle\Tests\AbstractTest;

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

        $this->assertSame(8, strlen($singer->sign('aPath')));
    }

    public function testShouldSingAndSuccessfullyCheckPathWithoutRuntimeConfig()
    {
        $singer = new Signer('aSecret');

        $this->assertTrue($singer->check($singer->sign('aPath'), 'aPath'));
    }

    public function testShouldSingAndSuccessfullyCheckPathWithRuntimeConfig()
    {
        $singer = new Signer('aSecret');

        $this->assertTrue($singer->check($singer->sign('aPath', ['aConfig']), 'aPath', ['aConfig']));
    }

    public function testShouldConvertRecursivelyToStringAllRuntimeConfigParameters()
    {
        $singer = new Signer('aSecret');

        $runtimeConfigInts = [
            'foo' => 14,
            'bar' => [
                'bar' => 15,
            ],
        ];

        $runtimeConfigStrings = [
            'foo' => '14',
            'bar' => [
                'bar' => '15',
            ],
        ];

        $this->assertTrue($singer->check($singer->sign('aPath', $runtimeConfigInts), 'aPath', $runtimeConfigStrings));
    }
}
