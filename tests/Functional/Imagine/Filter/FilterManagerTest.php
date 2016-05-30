<?php

namespace Anezi\ImagineBundle\Tests\Functional\Imagine\Filter;

use Anezi\ImagineBundle\Tests\Functional\WebTestCase;

class FilterManagerTest extends WebTestCase
{
    public function testCouldBeGetFromContainerAsService()
    {
        $this->createClient();
        $service = self::$kernel->getContainer()->get('anezi_imagine.filter.manager');

        $this->assertInstanceOf('Anezi\ImagineBundle\Imagine\Filter\FilterManager', $service);
    }
}
