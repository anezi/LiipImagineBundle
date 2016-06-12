<?php

namespace Anezi\ImagineBundle\Imagine\Data;

use Anezi\ImagineBundle\Binary\BinaryInterface;
use Anezi\ImagineBundle\Binary\Loader\LoaderInterface;
use Anezi\ImagineBundle\Binary\MimeTypeGuesserInterface;
use Anezi\ImagineBundle\Imagine\Filter\FilterConfiguration;
use Anezi\ImagineBundle\Model\Binary;
use Symfony\Component\HttpFoundation\File\MimeType\ExtensionGuesserInterface;

/**
 * Class DataManager.
 */
class DataManager
{
    /**
     * @var MimeTypeGuesserInterface
     */
    protected $mimeTypeGuesser;

    /**
     * @var ExtensionGuesserInterface
     */
    protected $extensionGuesser;

    /**
     * @var FilterConfiguration
     */
    protected $filterConfig;

    /**
     * @var string|null
     */
    protected $defaultLoader;

    /**
     * @var string|null
     */
    protected $globalDefaultImage;

    /**
     * @var LoaderInterface[]
     */
    protected $loaders = [];

    /**
     * @param MimeTypeGuesserInterface  $mimeTypeGuesser
     * @param ExtensionGuesserInterface $extensionGuesser
     * @param FilterConfiguration       $filterConfig
     * @param string                    $defaultLoader
     * @param string                    $globalDefaultImage
     */
    public function __construct(
        MimeTypeGuesserInterface $mimeTypeGuesser,
        ExtensionGuesserInterface $extensionGuesser,
        FilterConfiguration $filterConfig,
        $defaultLoader = null,
        $globalDefaultImage = null
    ) {
        $this->mimeTypeGuesser = $mimeTypeGuesser;
        $this->filterConfig = $filterConfig;
        $this->defaultLoader = $defaultLoader;
        $this->extensionGuesser = $extensionGuesser;
        $this->globalDefaultImage = $globalDefaultImage;
    }

    /**
     * Adds a loader to retrieve images for the given filter.
     *
     * @param string          $filter
     * @param LoaderInterface $loader
     */
    public function addLoader($filter, LoaderInterface $loader)
    {
        $this->loaders[$filter] = $loader;
    }

    /**
     * Returns a loader.
     *
     * @param string $loaderName
     *
     * @return LoaderInterface
     */
    public function getLoader(string $loaderName = null)
    {
        if (!isset($this->loaders[$loaderName ?: $this->defaultLoader])) {
            throw new \InvalidArgumentException(sprintf('Could not find data loader "%s"', $loaderName));
        }

        return $this->loaders[$loaderName];
    }

    /**
     * Retrieves an image with the given filter applied.
     *
     * @param LoaderInterface $loader
     * @param string          $path
     *
     * @return BinaryInterface
     */
    public function find(LoaderInterface $loader, $path)
    {
        $binary = $loader->find($path);

        if (!$binary instanceof BinaryInterface) {
            $mimeType = $this->mimeTypeGuesser->guess($binary);

            $binary = new Binary(
                $binary,
                $mimeType,
                $this->extensionGuesser->guess($mimeType)
            );
        }

        if (null === $binary->getMimeType()) {
            throw new \LogicException(sprintf('The mime type of image %s was not guessed.', $path));
        }

        if (0 !== strpos($binary->getMimeType(), 'image/')) {
            throw new \LogicException(sprintf('The mime type of image %s must be image/xxx got %s.', $path, $binary->getMimeType()));
        }

        return $binary;
    }

    /**
     * Get default image url with the given filter applied.
     *
     * @param string $filter
     *
     * @return string
     */
    public function getDefaultImageUrl($filter)
    {
        $config = $this->filterConfig->get($filter);

        $defaultImage = null;
        if (false === empty($config['default_image'])) {
            $defaultImage = $config['default_image'];
        } elseif (!empty($this->globalDefaultImage)) {
            $defaultImage = $this->globalDefaultImage;
        }

        return $defaultImage;
    }
}
