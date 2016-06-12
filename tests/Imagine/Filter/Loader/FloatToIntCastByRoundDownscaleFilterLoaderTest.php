<?php

namespace Anezi\ImagineBundle\Tests\Filter;

use Anezi\ImagineBundle\Imagine\Filter\Loader\DownscaleFilterLoader;
use Anezi\ImagineBundle\Tests\AbstractTest;
use Imagine\Gd\Imagine;

/**
 * @covers Anezi\ImagineBundle\Imagine\Filter\Loader\DownscaleFilterLoader
 *
 * Due to int casting in Imagine\Image\Box which can lead to wrong pixel
 * numbers ( e.g. float(201) casted to int(200) ). Solved by round the
 * floating number before passing to the Box constructor.
 */
class FloatToIntCastByRoundDownscaleFilterLoaderTest extends AbstractTest
{
    public function testLoad()
    {
        $loader = new DownscaleFilterLoader();
        $imagine = new Imagine();
        $image = $imagine->open(__DIR__.'/../../../Fixtures/assets/square-300x300.png');

        $options = [
            'max' => [201, 201],
        ];

        $image = $loader->load($image, $options);
        $size = $image->getSize();

        $this->assertSame($options['max'], [$size->getWidth(), $size->getHeight()]);
    }
}
