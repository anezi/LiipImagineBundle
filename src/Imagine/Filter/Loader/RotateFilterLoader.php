<?php

namespace Anezi\ImagineBundle\Imagine\Filter\Loader;

use Imagine\Image\ImageInterface;
use Imagine\Image\ManipulatorInterface;

/**
 * Loader for Imagine's basic rotate method.
 *
 * @author Bocharsky Victor <bocharsky.bw@gmail.com>
 */
class RotateFilterLoader implements LoaderInterface
{
    /**
     * Loads and applies a filter on the given image.
     *
     * @param ImageInterface $image
     * @param array          $options
     *
     * @return ManipulatorInterface
     */
    public function load(ImageInterface $image, array $options = [])
    {
        $angle = isset($options['angle']) ? (int) $options['angle'] : 0;

        return 0 === $angle ? $image : $image->rotate($angle);
    }
}
