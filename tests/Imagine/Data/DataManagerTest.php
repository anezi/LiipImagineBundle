<?php

namespace Anezi\ImagineBundle\tests\Imagine\Data;

use Anezi\ImagineBundle\Binary\Loader\LoaderInterface;
use Anezi\ImagineBundle\Imagine\Data\DataManager;
use Anezi\ImagineBundle\Model\Binary;
use Anezi\ImagineBundle\Tests\AbstractTest;

/**
 * @covers Anezi\ImagineBundle\Imagine\Data\DataManager
 */
class DataManagerTest extends AbstractTest
{
    /**
     * @test
     */
    public function testUseDefaultLoaderUsedIfNoneSet()
    {
        $loader = $this->getMockLoader();
        $loader
            ->expects($this->once())
            ->method('find')
            ->with('cats.jpeg');

        $config = $this->createFilterConfigurationMock();
        $config
            ->expects($this->once())
            ->method('get')
            ->with('thumbnail')
            ->will($this->returnValue([
                'size'        => [180, 180],
                'mode'        => 'outbound',
                'data_loader' => null,
            ]));

        $mimeTypeGuesser = $this->getMockMimeTypeGuesser();
        $mimeTypeGuesser
            ->expects($this->once())
            ->method('guess')
            ->will($this->returnValue('image/png'));

        $dataManager = new DataManager($mimeTypeGuesser, $this->getMockExtensionGuesser(), $config, 'default');
        $dataManager->addLoader('default', $loader);

        $dataManager->find('thumbnail', 'cats.jpeg');
    }

    /**
     * @test
     */
    public function testUseLoaderRegisteredForFilterOnFind()
    {
        $loader = $this->getMockLoader();
        $loader
            ->expects($this->once())
            ->method('find')
            ->with('cats.jpeg');

        $config = $this->createFilterConfigurationMock();
        $config
            ->expects($this->once())
            ->method('get')
            ->with('thumbnail')
            ->will($this->returnValue([
                'size'        => [180, 180],
                'mode'        => 'outbound',
                'data_loader' => 'the_loader',
            ]));

        $mimeTypeGuesser = $this->getMockMimeTypeGuesser();
        $mimeTypeGuesser
            ->expects($this->once())
            ->method('guess')
            ->will($this->returnValue('image/png'));

        $dataManager = new DataManager($mimeTypeGuesser, $this->getMockExtensionGuesser(), $config);
        $dataManager->addLoader('the_loader', $loader);

        $dataManager->find('thumbnail', 'cats.jpeg');
    }

    /**
     * @test
     */
    public function testThrowsIfMimeTypeWasNotGuessedOnFind()
    {
        $loader = $this->getMockLoader();
        $loader
            ->expects($this->once())
            ->method('find')
            ->with('cats.jpeg');

        $config = $this->createFilterConfigurationMock();
        $config
            ->expects($this->once())
            ->method('get')
            ->with('thumbnail')
            ->will($this->returnValue([
                'size'        => [180, 180],
                'mode'        => 'outbound',
                'data_loader' => 'the_loader',
            ]));

        $mimeTypeGuesser = $this->getMockMimeTypeGuesser();
        $mimeTypeGuesser
            ->expects($this->once())
            ->method('guess')
            ->will($this->returnValue(null));

        $dataManager = new DataManager($mimeTypeGuesser, $this->getMockExtensionGuesser(), $config);
        $dataManager->addLoader('the_loader', $loader);

        $this->setExpectedException('LogicException', 'The mime type of image cats.jpeg was not guessed.');
        $dataManager->find('thumbnail', 'cats.jpeg');
    }

    /**
     * @test
     */
    public function testThrowsIfMimeTypeNotImageOneOnFind()
    {
        $loader = $this->getMockLoader();
        $loader
            ->expects($this->once())
            ->method('find')
            ->with('cats.jpeg')
            ->will($this->returnValue('content'));

        $config = $this->createFilterConfigurationMock();
        $config
            ->expects($this->once())
            ->method('get')
            ->with('thumbnail')
            ->will($this->returnValue([
                'size'        => [180, 180],
                'mode'        => 'outbound',
                'data_loader' => 'the_loader',
            ]));

        $mimeTypeGuesser = $this->getMockMimeTypeGuesser();
        $mimeTypeGuesser
            ->expects($this->once())
            ->method('guess')
            ->will($this->returnValue('text/plain'));

        $dataManager = new DataManager($mimeTypeGuesser, $this->getMockExtensionGuesser(), $config);
        $dataManager->addLoader('the_loader', $loader);

        $this->setExpectedException('LogicException', 'The mime type of image cats.jpeg must be image/xxx got text/plain.');
        $dataManager->find('thumbnail', 'cats.jpeg');
    }

    /**
     * @test
     */
    public function testThrowsIfLoaderReturnBinaryWithEmtptyMimeTypeOnFind()
    {
        $loader = $this->getMockLoader();
        $loader
            ->expects($this->once())
            ->method('find')
            ->with('cats.jpeg')
            ->will($this->returnValue(new Binary('content', null)));

        $config = $this->createFilterConfigurationMock();
        $config
            ->expects($this->once())
            ->method('get')
            ->with('thumbnail')
            ->will($this->returnValue([
                'size'        => [180, 180],
                'mode'        => 'outbound',
                'data_loader' => 'the_loader',
            ]));

        $mimeTypeGuesser = $this->getMockMimeTypeGuesser();
        $mimeTypeGuesser
            ->expects($this->never())
            ->method('guess');

        $dataManager = new DataManager($mimeTypeGuesser, $this->getMockExtensionGuesser(), $config);
        $dataManager->addLoader('the_loader', $loader);

        $this->setExpectedException('LogicException', 'The mime type of image cats.jpeg was not guessed.');
        $dataManager->find('thumbnail', 'cats.jpeg');
    }

    /**
     * @test
     */
    public function testThrowsIfLoaderReturnBinaryWithMimeTypeNotImageOneOnFind()
    {
        $binary = new Binary('content', 'text/plain');

        $loader = $this->getMockLoader();
        $loader
            ->expects($this->once())
            ->method('find')
            ->with('cats.jpeg')
            ->will($this->returnValue($binary));

        $config = $this->createFilterConfigurationMock();
        $config
            ->expects($this->once())
            ->method('get')
            ->with('thumbnail')
            ->will($this->returnValue([
                'size'        => [180, 180],
                'mode'        => 'outbound',
                'data_loader' => 'the_loader',
            ]));

        $mimeTypeGuesser = $this->getMockMimeTypeGuesser();
        $mimeTypeGuesser
            ->expects($this->never())
            ->method('guess');

        $dataManager = new DataManager($mimeTypeGuesser, $this->getMockExtensionGuesser(), $config);
        $dataManager->addLoader('the_loader', $loader);

        $this->setExpectedException('LogicException', 'The mime type of image cats.jpeg must be image/xxx got text/plain.');
        $dataManager->find('thumbnail', 'cats.jpeg');
    }

    /**
     * @test
     */
    public function testThrowIfLoaderNotRegisteredForGivenFilterOnFind()
    {
        $config = $this->createFilterConfigurationMock();
        $config
            ->expects($this->once())
            ->method('get')
            ->with('thumbnail')
            ->will($this->returnValue([
                'size'        => [180, 180],
                'mode'        => 'outbound',
                'data_loader' => null,
            ]));

        $dataManager = new DataManager($this->getMockMimeTypeGuesser(), $this->getMockExtensionGuesser(), $config);

        $this->setExpectedException('InvalidArgumentException', 'Could not find data loader "" for "thumbnail" filter type');
        $dataManager->find('thumbnail', 'cats.jpeg');
    }

    /**
     * @test
     */
    public function testShouldReturnBinaryWithLoaderContentAndGuessedMimeTypeOnFind()
    {
        $expectedContent = 'theImageBinaryContent';
        $expectedMimeType = 'image/png';

        $loader = $this->getMockLoader();
        $loader
            ->expects($this->once())
            ->method('find')
            ->will($this->returnValue($expectedContent));

        $mimeTypeGuesser = $this->getMockMimeTypeGuesser();
        $mimeTypeGuesser
            ->expects($this->once())
            ->method('guess')
            ->with($expectedContent)
            ->will($this->returnValue($expectedMimeType));

        $config = $this->createFilterConfigurationMock();
        $config
            ->expects($this->once())
            ->method('get')
            ->with('thumbnail')
            ->will($this->returnValue([
                'size'        => [180, 180],
                'mode'        => 'outbound',
                'data_loader' => null,
            ]));

        $dataManager = new DataManager($mimeTypeGuesser, $this->getMockExtensionGuesser(), $config, 'default');
        $dataManager->addLoader('default', $loader);

        $binary = $dataManager->find('thumbnail', 'cats.jpeg');

        $this->assertInstanceOf('Anezi\ImagineBundle\Model\Binary', $binary);
        $this->assertSame($expectedContent, $binary->getContent());
        $this->assertSame($expectedMimeType, $binary->getMimeType());
    }

    /**
     * @test
     */
    public function testShouldReturnBinaryWithLoaderContentAndGuessedFormatOnFind()
    {
        $content = 'theImageBinaryContent';
        $mimeType = 'image/png';
        $expectedFormat = 'png';

        $loader = $this->getMockLoader();
        $loader
            ->expects($this->once())
            ->method('find')
            ->will($this->returnValue($content));

        $mimeTypeGuesser = $this->getMockMimeTypeGuesser();
        $mimeTypeGuesser
            ->expects($this->once())
            ->method('guess')
            ->with($content)
            ->will($this->returnValue($mimeType));

        $extensionGuesser = $this->getMockExtensionGuesser();
        $extensionGuesser
            ->expects($this->once())
            ->method('guess')
            ->with($mimeType)
            ->will($this->returnValue($expectedFormat));

        $config = $this->createFilterConfigurationMock();
        $config
            ->expects($this->once())
            ->method('get')
            ->with('thumbnail')
            ->will($this->returnValue([
                'size'        => [180, 180],
                'mode'        => 'outbound',
                'data_loader' => null,
            ]));

        $dataManager = new DataManager($mimeTypeGuesser, $extensionGuesser, $config, 'default');
        $dataManager->addLoader('default', $loader);

        $binary = $dataManager->find('thumbnail', 'cats.jpeg');

        $this->assertInstanceOf('Anezi\ImagineBundle\Model\Binary', $binary);
        $this->assertSame($expectedFormat, $binary->getFormat());
    }

    /**
     * @test
     */
    public function testUseDefaultGlobalImageUsedIfImageNotFound()
    {
        $loader = $this->getMockLoader();

        $config = $this->createFilterConfigurationMock();
        $config
            ->expects($this->once())
            ->method('get')
            ->with('thumbnail')
            ->will($this->returnValue([
                'default_image' => null,
            ]));

        $mimeTypeGuesser = $this->getMockMimeTypeGuesser();
        $mimeTypeGuesser
            ->expects($this->never())
            ->method('guess');

        $defaultGlobalImage = 'cats.jpeg';
        $dataManager = new DataManager($mimeTypeGuesser, $this->getMockExtensionGuesser(), $config, 'default', 'cats.jpeg');
        $dataManager->addLoader('default', $loader);

        $defaultImage = $dataManager->getDefaultImageUrl('thumbnail');
        $this->assertSame($defaultImage, $defaultGlobalImage);
    }

    /**
     * @test
     */
    public function testUseDefaultFilterImageUsedIfImageNotFound()
    {
        $loader = $this->getMockLoader();

        $defaultFilterImage = 'cats.jpeg';

        $config = $this->createFilterConfigurationMock();
        $config
            ->expects($this->once())
            ->method('get')
            ->with('thumbnail')
            ->will($this->returnValue([
                'default_image' => $defaultFilterImage,
            ]));

        $mimeTypeGuesser = $this->getMockMimeTypeGuesser();
        $mimeTypeGuesser
            ->expects($this->never())
            ->method('guess');

        $dataManager = new DataManager($mimeTypeGuesser, $this->getMockExtensionGuesser(), $config, 'default', null);
        $dataManager->addLoader('default', $loader);

        $defaultImage = $dataManager->getDefaultImageUrl('thumbnail');
        $this->assertSame($defaultImage, $defaultFilterImage);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|LoaderInterface
     */
    protected function getMockLoader()
    {
        return $this->getMock('Anezi\ImagineBundle\Binary\Loader\LoaderInterface');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Anezi\ImagineBundle\Binary\MimeTypeGuesserInterface
     */
    protected function getMockMimeTypeGuesser()
    {
        return $this->getMock('Anezi\ImagineBundle\Binary\MimeTypeGuesserInterface');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Symfony\Component\HttpFoundation\File\MimeType\ExtensionGuesserInterface
     */
    protected function getMockExtensionGuesser()
    {
        return $this->getMock('Symfony\Component\HttpFoundation\File\MimeType\ExtensionGuesserInterface');
    }
}
