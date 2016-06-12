<?php

namespace Anezi\ImagineBundle\tests\Functional\Imagine\Filter\Loader;

use Anezi\ImagineBundle\Tests\Functional\WebTestCase;

/**
 * Functional test cases for RotateFilterLoader class.
 *
 * @author Bocharsky Victor <bocharsky.bw@gmail.com>
 */
class RotateFilterLoaderTest extends WebTestCase
{
    public function testCouldBeGetFromContainerAsService()
    {
        $this->createClient();
        $service = self::$kernel->getContainer()->get('anezi_imagine.filter.loader.rotate');

        $this->assertInstanceOf('Anezi\ImagineBundle\Imagine\Filter\Loader\RotateFilterLoader', $service);
    }
}
