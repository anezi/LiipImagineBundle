<?php

namespace Anezi\ImagineBundle\Imagine\Filter\Loader;

use Anezi\ImagineBundle\Imagine\Filter\RelativeResize;
use Imagine\Exception\InvalidArgumentException;
use Imagine\Image\ImageInterface;

/**
 * Loader for this bundle's relative resize filter.
 *
 * @author Jeremy Mikola <jmikola@gmail.com>
 */
class RelativeResizeFilterLoader implements LoaderInterface
{
    /**
     * {@inheritdoc}
     */
    public function load(ImageInterface $image, array $options = [])
    {
        if (list($method, $parameter) = each($options)) {
            $filter = new RelativeResize($method, $parameter);

            return $filter->apply($image);
        }

        throw new InvalidArgumentException('Expected method/parameter pair, none given');
    }
}
