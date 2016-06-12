<?php

namespace Anezi\ImagineBundle\tests\Imagine\Cache\Resolver;

use Anezi\ImagineBundle\Imagine\Cache\Resolver\NoCacheWebPathResolver;
use Anezi\ImagineBundle\Model\Binary;
use Anezi\ImagineBundle\Tests\AbstractTest;
use Symfony\Component\Routing\RequestContext;

/**
 * @covers Anezi\ImagineBundle\Imagine\Cache\Resolver\NoCacheWebPathResolver
 */
class NoCacheWebPathResolverTest extends AbstractTest
{
    /**
     * @test
     */
    public function testCouldBeConstructedWithRequestContextAsArgument()
    {
        new NoCacheWebPathResolver(new RequestContext());
    }

    /**
     * @test
     */
    public function testComposeSchemaHostAndPathOnResolve()
    {
        $context = new RequestContext('', 'GET', 'thehost', 'theSchema');

        $resolver = new NoCacheWebPathResolver($context);

        $this->assertSame('theschema://thehost/aPath', $resolver->resolve('aPath', 'aLoader', 'aFilter'));
    }

    /**
     * @test
     */
    public function testDoNothingOnStore()
    {
        $resolver = new NoCacheWebPathResolver(new RequestContext());

        $this->assertNull($resolver->store(
            new Binary('aContent', 'image/jpeg', 'jpg'),
            'a/path',
            'aLoader',
            'aFilter'
        ));
    }

    /**
     * @test
     */
    public function testDoNothingForPathAndFilterOnRemove()
    {
        $resolver = new NoCacheWebPathResolver(new RequestContext());

        $resolver->remove(['a/path'], ['aLoader'], ['aFilter']);
    }

    /**
     * @test
     */
    public function testDoNothingForSomePathsAndSomeFiltersOnRemove()
    {
        $resolver = new NoCacheWebPathResolver(new RequestContext());

        $resolver->remove(['foo', 'bar'], ['aLoader'], ['foo', 'bar']);
    }

    /**
     * @test
     */
    public function testDoNothingForEmptyPathAndEmptyFilterOnRemove()
    {
        $resolver = new NoCacheWebPathResolver(new RequestContext());

        $resolver->remove([], [], []);
    }
}
