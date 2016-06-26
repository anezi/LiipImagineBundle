<?php

namespace Anezi\ImagineBundle\Twig;

use Anezi\ImagineBundle\Imagine\Cache\CacheManager;

/**
 * Class ImagineExtension.
 *
 * @author  Hassan Amouhzi <hassan@amouhzi.com>
 * @license Proprietary See License file.
 */
class ImagineExtension extends \Twig_Extension
{
    /**
     * @var CacheManager
     */
    private $cacheManager;

    /**
     * Constructor.
     *
     * @param CacheManager $cacheManager
     */
    public function __construct(CacheManager $cacheManager)
    {
        $this->cacheManager = $cacheManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('imagine_filter', [$this, 'filter']),
        ];
    }

    /**
     * Gets the browser path for the image and filter to apply.
     *
     * @param string $path
     * @param string $loader
     * @param string $filter
     *
     * @return \Twig_Markup
     */
    public function filter(string $path, string $loader, string $filter)
    {
        return $this->cacheManager->getBrowserPath($path, $loader, $filter);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'anezi_imagine';
    }
}
