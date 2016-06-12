<?php

namespace Anezi\ImagineBundle\Imagine\Filter\Loader;

use Imagine\Filter\Basic\Resize;
use Imagine\Image\Box;
use Imagine\Image\ImageInterface;

/**
 * downscale filter.
 */
class DownscaleFilterLoader implements LoaderInterface
{
    /**
     * {@inheritdoc}
     */
    public function load(ImageInterface $image, array $options = [])
    {
        if (!isset($options['max'])) {
            throw new \InvalidArgumentException('Missing max option.');
        }

        list($width, $height) = $options['max'];

        $size = $image->getSize();
        $origWidth = $size->getWidth();
        $origHeight = $size->getHeight();

        if ($origWidth > $width || $origHeight > $height) {
            $widthRatio = $width / $origWidth;
            $heightRatio = $height / $origHeight;

            $ratio = $widthRatio > $heightRatio ? $widthRatio : $heightRatio;

            $filter = new Resize(new Box($origWidth * $ratio, $origHeight * $ratio));

            return $filter->apply($image);
        }

        return $image;
    }
}
