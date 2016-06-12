<?php

namespace Anezi\ImagineBundle\Events;

use Symfony\Component\EventDispatcher\Event;

/**
 * Class CacheResolveEvent.
 */
class CacheResolveEvent extends Event
{
    /**
     * Resource path.
     *
     * @var string
     */
    protected $path;

    /**
     * @var string
     */
    protected $loader;

    /**
     * Filter name.
     *
     * @var string
     */
    protected $filter;

    /**
     * Resource url.
     *
     * @var null
     */
    protected $url;

    /**
     * Init default event state.
     *
     * @param string      $path
     * @param string      $loader
     * @param string      $filter
     * @param null|string $url
     */
    public function __construct(string $path, string $loader, string $filter, string $url = null)
    {
        $this->path = $path;
        $this->loader = $loader;
        $this->filter = $filter;
        $this->url = $url;
    }

    /**
     * Sets resource path.
     *
     * @param $path
     */
    public function setPath($path)
    {
        $this->path = $path;
    }

    /**
     * Returns resource path.
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Gets the loader.
     *
     * @return string
     */
    public function getLoader() : string
    {
        return $this->loader;
    }

    /**
     * Sets the loader.
     *
     * @param string $loader
     */
    public function setLoader(string $loader)
    {
        $this->loader = $loader;
    }

    /**
     * Sets filter name.
     *
     * @param $filter
     */
    public function setFilter($filter)
    {
        $this->filter = $filter;
    }

    /**
     * Returns filter name.
     *
     * @return string
     */
    public function getFilter()
    {
        return $this->filter;
    }

    /**
     * Sets resource url.
     *
     * @param $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * Returns resource url.
     */
    public function getUrl()
    {
        return $this->url;
    }
}
