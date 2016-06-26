<?php

namespace Anezi\ImagineBundle\Binary\Loader;

/**
 * Interface LoaderInterface.
 */
interface LoaderInterface
{
    /**
     * Retrieve the Image represented by the given path.
     *
     * The path may be a file path on a filesystem, or any unique identifier among the storage engine implemented by this Loader.
     *
     * @param mixed $path
     *
     * @return \Anezi\ImagineBundle\Binary\BinaryInterface|string An image binary content
     */
    public function find($path);
}
