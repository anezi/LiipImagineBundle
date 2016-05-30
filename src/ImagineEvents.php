<?php

namespace Anezi\ImagineBundle;

interface ImagineEvents
{
    /**
     * @Event("Anezi\ImagineBundle\Events\CacheResolveEvent")
     */
    const PRE_RESOLVE = 'anezi_imagine.pre_resolve';

    /**
     * @Event("Anezi\ImagineBundle\Events\CacheResolveEvent")
     */
    const POST_RESOLVE = 'anezi_imagine.post_resolve';
}
