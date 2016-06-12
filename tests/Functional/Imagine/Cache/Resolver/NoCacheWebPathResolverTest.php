<?php

namespace Anezi\ImagineBundle\tests\Functional\Imagine\Cache\Resolver;

use Anezi\ImagineBundle\Tests\Functional\WebTestCase;

/**
 * @covers Anezi\ImagineBundle\Imagine\Cache\Resolver\NoCacheWebPathResolver
 */
class NoCacheWebPathResolverTest extends WebTestCase
{
    public function testCouldBeGetFromContainer()
    {
        $this->createClient();

        $resolver = self::$kernel->getContainer()->get('anezi_imagine.cache.resolver.no_cache_web_path');

        $this->assertInstanceOf('Anezi\ImagineBundle\Imagine\Cache\Resolver\NoCacheWebPathResolver', $resolver);
    }
}
