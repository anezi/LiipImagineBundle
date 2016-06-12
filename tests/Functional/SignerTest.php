<?php

namespace Anezi\ImagineBundle\tests\Functional;

class SignerTest extends WebTestCase
{
    public function testGetAsService()
    {
        $this->createClient();
        $service = self::$kernel->getContainer()->get('anezi_imagine.cache.signer');

        $this->assertInstanceOf('Anezi\ImagineBundle\Imagine\Cache\SignerInterface', $service);
    }
}
