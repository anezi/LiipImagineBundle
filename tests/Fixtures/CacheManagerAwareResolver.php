<?php

namespace Anezi\ImagineBundle\Tests\Fixtures;

use Anezi\ImagineBundle\Imagine\Cache\CacheManagerAwareInterface;
use Anezi\ImagineBundle\Imagine\Cache\Resolver\ResolverInterface;

abstract class CacheManagerAwareResolver implements ResolverInterface, CacheManagerAwareInterface
{
}
