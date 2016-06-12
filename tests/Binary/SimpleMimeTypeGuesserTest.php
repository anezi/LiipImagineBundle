<?php

namespace Anezi\ImagineBundle\tests\Binary;

use Anezi\ImagineBundle\Binary\SimpleMimeTypeGuesser;
use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesser;

/**
 * @covers Anezi\ImagineBundle\Binary\SimpleMimeTypeGuesser<extended>
 */
class SimpleMimeTypeGuesserTest extends \PHPUnit_Framework_TestCase
{
    public function provideImages()
    {
        return [
            'gif' => [__DIR__.'/../Fixtures/assets/cats.gif', 'image/gif'],
            'png' => [__DIR__.'/../Fixtures/assets/cats.png', 'image/png'],
            'jpg' => [__DIR__.'/../Fixtures/assets/cats.jpeg', 'image/jpeg'],
            'pdf' => [__DIR__.'/../Fixtures/assets/cats.pdf', 'application/pdf'],
            'txt' => [__DIR__.'/../Fixtures/assets/cats.txt', 'text/plain'],
        ];
    }

    public function testImplementsMimeTypeGuesserInterface()
    {
        $rc = new \ReflectionClass('Anezi\ImagineBundle\Binary\SimpleMimeTypeGuesser');

        $this->assertTrue($rc->implementsInterface('Anezi\ImagineBundle\Binary\MimeTypeGuesserInterface'));
    }

    public function testCouldBeConstructedWithSymfonyMimeTypeGuesserAsFirstArgument()
    {
        new SimpleMimeTypeGuesser(MimeTypeGuesser::getInstance());
    }

    /**
     * @dataProvider provideImages
     */
    public function testGuessMimeType($imageFile, $expectedMimeType)
    {
        $guesser = new SimpleMimeTypeGuesser(MimeTypeGuesser::getInstance());

        $this->assertSame($expectedMimeType, $guesser->guess(file_get_contents($imageFile)));
    }
}
