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
        return array(
            new \Twig_SimpleFilter('imagine_filter', array($this, 'filter')),
        );
    }

    /**
     * Gets the browser path for the image and filter to apply.
     *
     * @param string $path
     * @param string $filter
     * @param array  $runtimeConfig
     * @param string $resolver
     *
     * @return \Twig_Markup
     */
    public function filter($path, $filter, array $runtimeConfig = array(), $resolver = null)
    {
        return $this->cacheManager->getBrowserPath($path, $filter, $runtimeConfig, $resolver);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'anezi_imagine';
    }
}
