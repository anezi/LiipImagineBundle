<?php

namespace Anezi\ImagineBundle\tests\Functional\Imagine\Data;

use Anezi\ImagineBundle\Tests\Functional\WebTestCase;

class DataManagerTest extends WebTestCase
{
    public function testCouldBeGetFromContainerAsService()
    {
        $this->createClient();
        $service = self::$kernel->getContainer()->get('anezi_imagine.data.manager');

        $this->assertInstanceOf('Anezi\ImagineBundle\Imagine\Data\DataManager', $service);
    }
}
