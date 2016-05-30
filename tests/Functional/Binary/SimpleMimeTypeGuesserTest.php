<?php

namespace Anezi\ImagineBundle\Tests\Functional\Binary;

use Anezi\ImagineBundle\Tests\Functional\WebTestCase;

class SimpleMimeTypeGuesserTest extends WebTestCase
{
    public function testCouldBeGetFromContainerAsService()
    {
        $this->createClient();

        $service = self::$kernel->getContainer()->get('anezi_imagine.binary.mime_type_guesser');

        $this->assertInstanceOf('Anezi\ImagineBundle\Binary\SimpleMimeTypeGuesser', $service);
    }
}
