<?php

namespace Anezi\ImagineBundle\Tests\Filter;

use Anezi\ImagineBundle\Imagine\Filter\Loader\InterlaceFilterLoader;
use Anezi\ImagineBundle\Tests\AbstractTest;

/**
 * @covers Anezi\ImagineBundle\Imagine\Filter\Loader\InterlaceFilterLoader
 */
class InterlaceFilterLoaderTest extends AbstractTest
{
    public function testLoad()
    {
        $loader = new InterlaceFilterLoader();

        $image = $this->getMockImage();
        $image
            ->expects($this->once())
            ->method('interlace')
            ->with('TEST');

        $result = $loader->load($image, ['mode' => 'TEST']);

        $this->assertInstanceOf('Imagine\Image\ImageInterface', $result);
    }
}
