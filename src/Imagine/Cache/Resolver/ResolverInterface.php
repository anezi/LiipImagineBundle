<?php

namespace Anezi\ImagineBundle\Imagine\Cache\Resolver;

use Anezi\ImagineBundle\Binary\BinaryInterface;

/**
 * Interface ResolverInterface.
 */
interface ResolverInterface
{
    /**
     * Checks whether the given path is stored within this Resolver.
     *
     * @param string $path
     * @param string $loader
     * @param string $filter
     *
     * @return bool
     */
    public function isStored(string $path, string $loader, string $filter) : bool;

    /**
     * Resolves filtered path for rendering in the browser.
     *
     * @param string $path   The path where the original file is expected to be.
     * @param string $loader
     * @param string $filter The name of the imagine filter in effect.
     *
     * @return string The absolute URL of the cached image.
     */
    public function resolve(string $path, string $loader, string $filter) : string;

    /**
     * Stores the content of the given binary.
     *
     * @param BinaryInterface $binary The image binary to store.
     * @param string          $path   The path where the original file is expected to be.
     * @param string          $loader
     * @param string          $filter The name of the imagine filter in effect.
     */
    public function store(BinaryInterface $binary, string $path, string $loader, string $filter);

    /**
     * @param string[] $paths   The paths where the original files are expected to be.
     * @param string[] $loaders
     * @param string[] $filters The imagine filters in effect.
     */
    public function remove(array $paths, array $loaders, array $filters);

    /**
     * @param string $path
     * @param string $loader
     * @param string $filter
     *
     * @return mixed
     */
    public function fetch(string $path, string $loader, string $filter) : string;
}
