<?php

namespace Anezi\ImagineBundle\tests\Binary\Loader;

use Anezi\ImagineBundle\Binary\Loader\FileSystemLoader;
use Symfony\Component\HttpFoundation\File\MimeType\ExtensionGuesser;
use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesser;

/**
 * @covers Anezi\ImagineBundle\Binary\Loader\FileSystemLoader
 */
class FileSystemLoaderTest extends \PHPUnit_Framework_TestCase
{
    public static function provideLoadCases()
    {
        $fileName = pathinfo(__FILE__, PATHINFO_BASENAME);

        return [
            [__DIR__, $fileName],
            [__DIR__.'/', $fileName],
            [__DIR__, '/'.$fileName],
            [__DIR__.'/', '/'.$fileName],
            [realpath(__DIR__.'/..'), 'Loader/'.$fileName],
            [realpath(__DIR__.'/../'), '/Loader/'.$fileName],
        ];
    }

    public function testShouldImplementLoaderInterface()
    {
        $rc = new \ReflectionClass('Anezi\ImagineBundle\Binary\Loader\FileSystemLoader');

        $this->assertTrue($rc->implementsInterface('Anezi\ImagineBundle\Binary\Loader\LoaderInterface'));
    }

    public function testCouldBeConstructedWithExpectedArguments()
    {
        new FileSystemLoader(
            MimeTypeGuesser::getInstance(),
            ExtensionGuesser::getInstance(),
            __DIR__
        );
    }

    public function testThrowExceptionIfPathHasDoublePointSlashAtBegging()
    {
        $loader = new FileSystemLoader(
            MimeTypeGuesser::getInstance(),
            ExtensionGuesser::getInstance(),
            __DIR__
        );

        $this->setExpectedException(
            'Anezi\ImagineBundle\Exception\Binary\Loader\NotLoadableException',
            'Source image was searched with'
        );

        $loader->find('../foo/bar');
    }

    public function testThrowExceptionIfPathHasDoublePointSlashInTheMiddle()
    {
        $loader = new FileSystemLoader(
            MimeTypeGuesser::getInstance(),
            ExtensionGuesser::getInstance(),
            __DIR__
        );

        $this->setExpectedException(
            'Anezi\ImagineBundle\Exception\Binary\Loader\NotLoadableException',
            'Source image was searched with'
        );

        $loader->find('foo/../bar');
    }

    public function testThrowExceptionIfFileNotExist()
    {
        $loader = new FileSystemLoader(
            MimeTypeGuesser::getInstance(),
            ExtensionGuesser::getInstance(),
            __DIR__
        );

        $this->setExpectedException(
            'Anezi\ImagineBundle\Exception\Binary\Loader\NotLoadableException',
            'Source image not found'
        );

        $loader->find('fileNotExist');
    }

    /**
     * @dataProvider provideLoadCases
     */
    public function testLoad($rootDir, $path)
    {
        $loader = new FileSystemLoader(
            MimeTypeGuesser::getInstance(),
            ExtensionGuesser::getInstance(),
            $rootDir
        );

        $binary = $loader->find($path);

        $this->assertInstanceOf('Anezi\ImagineBundle\Model\FileBinary', $binary);
        $this->assertStringStartsWith('text/', $binary->getMimeType());
    }
}
