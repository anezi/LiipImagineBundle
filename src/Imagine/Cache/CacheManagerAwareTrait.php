<?php

namespace Anezi\ImagineBundle\Imagine\Cache;

trait CacheManagerAwareTrait
{
    /**
     * @var CacheManager
     */
    protected $cacheManager;

    public function setCacheManager(CacheManager $cacheManager)
    {
        $this->cacheManager = $cacheManager;
    }
}
