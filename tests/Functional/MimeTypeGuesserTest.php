<?php

namespace Anezi\ImagineBundle\Tests\Functional;

class MimeTypeGuesserTest extends WebTestCase
{
    public function testCouldBeGetFromContainerAsService()
    {
        $this->createClient();
        $guesser = self::$kernel->getContainer()->get('anezi_imagine.mime_type_guesser');

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesser', $guesser);
    }
}
