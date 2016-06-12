<?php

namespace Anezi\ImagineBundle\tests\Imagine\Cache\Resolver;

use Anezi\ImagineBundle\Imagine\Cache\Resolver\WebPathResolver;
use Anezi\ImagineBundle\Model\Binary;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Routing\RequestContext;

/**
 * @covers Anezi\ImagineBundle\Imagine\Cache\Resolver\WebPathResolver
 */
class WebPathResolverTest extends \PHPUnit_Framework_TestCase
{
    /** @var Filesystem */
    private $filesystem;

    /** @var string */
    private $basePath;

    /** @var string */
    private $existingFile;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->filesystem = new Filesystem();
        $this->basePath = sys_get_temp_dir().'/aWebRoot';
        $this->existingFile = $this->basePath.'/aCachePrefix/aFilter/existingPath';
        $this->filesystem->mkdir(dirname($this->existingFile));
        $this->filesystem->touch($this->existingFile);
    }

    /**
     * {@inheritdoc}
     */
    public function tearDown()
    {
        $this->filesystem->remove($this->basePath);
    }

    /**
     * @test
     */
    public function testImplementsResolverInterface()
    {
        $rc = new \ReflectionClass('Anezi\ImagineBundle\Imagine\Cache\Resolver\WebPathResolver');

        $this->assertTrue($rc->implementsInterface('Anezi\ImagineBundle\Imagine\Cache\Resolver\ResolverInterface'));
    }

    /**
     * @test
     */
    public function testCouldBeConstructedWithRequiredArguments()
    {
        $filesystemMock = $this->createFilesystemMock();
        $requestContext = new RequestContext();
        $webRoot = 'theWebRoot';

        $resolver = new WebPathResolver($filesystemMock, $requestContext, $webRoot);

        $this->assertAttributeSame($filesystemMock, 'filesystem', $resolver);
        $this->assertAttributeSame($requestContext, 'requestContext', $resolver);
        $this->assertAttributeSame($webRoot, 'webRoot', $resolver);
    }

    /**
     * @test
     */
    public function testCouldBeConstructedWithOptionalArguments()
    {
        $resolver = new WebPathResolver(
            $this->createFilesystemMock(),
            new RequestContext(),
            'aWebRoot',
            'theCachePrefix'
        );

        $this->assertAttributeSame('theCachePrefix', 'cachePrefix', $resolver);
    }

    /**
     * @test
     */
    public function testTrimRightSlashFromWebPathOnConstruct()
    {
        $resolver = new WebPathResolver(
            $this->createFilesystemMock(),
            new RequestContext(),
            'aWebRoot/',
            'theCachePrefix'
        );

        $this->assertAttributeSame('aWebRoot', 'webRoot', $resolver);
    }

    /**
     * @test
     */
    public function testRemoveDoubleSlashFromWebRootOnConstruct()
    {
        $resolver = new WebPathResolver(
            $this->createFilesystemMock(),
            new RequestContext(),
            'aWeb//Root',
            '/aCachePrefix'
        );

        $this->assertAttributeSame('aWeb/Root', 'webRoot', $resolver);
    }

    /**
     * @test
     */
    public function testTrimRightSlashFromCachePrefixOnConstruct()
    {
        $resolver = new WebPathResolver(
            $this->createFilesystemMock(),
            new RequestContext(),
            'aWebRoot',
            '/aCachePrefix'
        );

        $this->assertAttributeSame('aCachePrefix', 'cachePrefix', $resolver);
    }

    /**
     * @test
     */
    public function testRemoveDoubleSlashFromCachePrefixOnConstruct()
    {
        $resolver = new WebPathResolver(
            $this->createFilesystemMock(),
            new RequestContext(),
            'aWebRoot',
            'aCache//Prefix'
        );

        $this->assertAttributeSame('aCache/Prefix', 'cachePrefix', $resolver);
    }

    /**
     * @test
     */
    public function testReturnTrueIfFileExistsOnIsStored()
    {
        $resolver = new WebPathResolver(
            $this->createFilesystemMock(),
            new RequestContext(),
            $this->basePath,
            'aCachePrefix'
        );

        $this->assertTrue($resolver->isStored('existingPath', 'aLoader', 'aFilter'));
    }

    /**
     * @test
     */
    public function testReturnFalseIfFileNotExistsOnIsStored()
    {
        $resolver = new WebPathResolver(
            $this->createFilesystemMock(),
            new RequestContext(),
            $this->basePath,
            'aCachePrefix'
        );

        $this->assertFalse($resolver->isStored('nonExistingPath', 'aLoader', 'aFilter'));
    }

    /**
     * @test
     */
    public function testReturnFalseIfIsNotFile()
    {
        $resolver = new WebPathResolver(
            $this->createFilesystemMock(),
            new RequestContext(),
            $this->basePath,
            'aCachePrefix'
        );

        $this->assertFalse($resolver->isStored('', 'aLoader', 'aFilter'));
    }

    /**
     * @test
     */
    public function testComposeSchemaHostAndFileUrlOnResolve()
    {
        $requestContext = new RequestContext();
        $requestContext->setScheme('theSchema');
        $requestContext->setHost('thehost');

        $resolver = new WebPathResolver(
            $this->createFilesystemMock(),
            $requestContext,
            '/aWebRoot',
            'aCachePrefix'
        );

        $this->assertSame(
            'theschema://thehost/aCachePrefix/aFilter/aPath',
            $resolver->resolve('aPath', 'aLoader', 'aFilter')
        );
    }

    /**
     * @test
     */
    public function testComposeSchemaHostAndBasePathWithPhpFileAndFileUrlOnResolve()
    {
        $requestContext = new RequestContext();
        $requestContext->setScheme('theSchema');
        $requestContext->setHost('thehost');
        $requestContext->setBaseUrl('/theBasePath/app.php');

        $resolver = new WebPathResolver(
            $this->createFilesystemMock(),
            $requestContext,
            '/aWebRoot',
            'aCachePrefix'
        );

        $this->assertSame(
            'theschema://thehost/theBasePath/aCachePrefix/aFilter/aPath',
            $resolver->resolve('aPath', 'aLoader', 'aFilter')
        );
    }

    /**
     * @test
     */
    public function testComposeSchemaHostAndBasePathWithDirsOnlyAndFileUrlOnResolve()
    {
        $requestContext = new RequestContext();
        $requestContext->setScheme('theSchema');
        $requestContext->setHost('thehost');
        $requestContext->setBaseUrl('/theBasePath/theSubBasePath');

        $resolver = new WebPathResolver(
            $this->createFilesystemMock(),
            $requestContext,
            '/aWebRoot',
            'aCachePrefix'
        );

        $this->assertSame(
            'theschema://thehost/theBasePath/theSubBasePath/aCachePrefix/aFilter/aPath',
            $resolver->resolve('aPath', 'aLoader', 'aFilter')
        );
    }

    /**
     * @test
     */
    public function testComposeSchemaHostAndBasePathWithBackSplashOnResolve()
    {
        $requestContext = new RequestContext();
        $requestContext->setScheme('theSchema');
        $requestContext->setHost('thehost');
        $requestContext->setBaseUrl('\\');

        $resolver = new WebPathResolver(
            $this->createFilesystemMock(),
            $requestContext,
            '/aWebRoot',
            'aCachePrefix'
        );

        $this->assertSame(
            'theschema://thehost/aCachePrefix/aFilter/aPath',
            $resolver->resolve('aPath', 'aLoader', 'aFilter')
        );
    }

    /**
     * @test
     */
    public function testComposeSchemaHttpAndCustomPortAndFileUrlOnResolve()
    {
        $requestContext = new RequestContext();
        $requestContext->setScheme('http');
        $requestContext->setHost('thehost');
        $requestContext->setHttpPort(88);

        $resolver = new WebPathResolver(
            $this->createFilesystemMock(),
            $requestContext,
            '/aWebRoot',
            'aCachePrefix'
        );

        $this->assertSame(
            'http://thehost:88/aCachePrefix/aFilter/aPath',
            $resolver->resolve('aPath', 'aLoader', 'aFilter')
        );
    }

    /**
     * @test
     */
    public function testComposeSchemaHttpsAndCustomPortAndFileUrlOnResolve()
    {
        $requestContext = new RequestContext();
        $requestContext->setScheme('https');
        $requestContext->setHost('thehost');
        $requestContext->setHttpsPort(444);

        $resolver = new WebPathResolver(
            $this->createFilesystemMock(),
            $requestContext,
            '/aWebRoot',
            'aCachePrefix'
        );

        $this->assertSame(
            'https://thehost:444/aCachePrefix/aFilter/aPath',
            $resolver->resolve('aPath', 'aLoader', 'aFilter')
        );
    }

    /**
     * @test
     */
    public function testDumpBinaryContentOnStore()
    {
        $binary = new Binary('theContent', 'aMimeType', 'aFormat');

        $filesystemMock = $this->createFilesystemMock();
        $filesystemMock
            ->expects($this->once())
            ->method('dumpFile')
            ->with('/aWebRoot/aCachePrefix/aFilter/aPath', 'theContent');

        $resolver = new WebPathResolver(
            $filesystemMock,
            new RequestContext(),
            '/aWebRoot',
            'aCachePrefix'
        );

        $this->assertNull($resolver->store($binary, 'aPath', 'aLoader', 'aFilter'));
    }

    /**
     * @test
     */
    public function testDoNothingIfFiltersAndPathsEmptyOnRemove()
    {
        $filesystemMock = $this->createFilesystemMock();
        $filesystemMock
            ->expects($this->never())
            ->method('remove');

        $resolver = new WebPathResolver(
            $filesystemMock,
            new RequestContext(),
            '/aWebRoot',
            'aCachePrefix'
        );

        $resolver->remove([], [], []);
    }

    /**
     * @test
     */
    public function testRemoveCacheForPathAndFilterOnRemove()
    {
        $filesystemMock = $this->createFilesystemMock();
        $filesystemMock
            ->expects($this->once())
            ->method('remove')
            ->with('/aWebRoot/aCachePrefix/aFilter/aPath');

        $resolver = new WebPathResolver(
            $filesystemMock,
            new RequestContext(),
            '/aWebRoot',
            'aCachePrefix'
        );

        $resolver->remove(['aPath'], ['aLoader'], ['aFilter']);
    }

    /**
     * @test
     */
    public function testRemoveCacheForSomePathsAndFilterOnRemove()
    {
        $filesystemMock = $this->createFilesystemMock();
        $filesystemMock
            ->expects($this->at(0))
            ->method('remove')
            ->with('/aWebRoot/aCachePrefix/aFilter/aPathOne');
        $filesystemMock
            ->expects($this->at(1))
            ->method('remove')
            ->with('/aWebRoot/aCachePrefix/aFilter/aPathTwo');

        $resolver = new WebPathResolver(
            $filesystemMock,
            new RequestContext(),
            '/aWebRoot',
            'aCachePrefix'
        );

        $resolver->remove(['aPathOne', 'aPathTwo'], ['aLoader'], ['aFilter']);
    }

    /**
     * @test
     */
    public function testRemoveCacheForSomePathsAndSomeFiltersOnRemove()
    {
        $filesystemMock = $this->createFilesystemMock();
        $filesystemMock
            ->expects($this->at(0))
            ->method('remove')
            ->with('/aWebRoot/aCachePrefix/aFilterOne/aPathOne');
        $filesystemMock
            ->expects($this->at(1))
            ->method('remove')
            ->with('/aWebRoot/aCachePrefix/aFilterTwo/aPathOne');
        $filesystemMock
            ->expects($this->at(2))
            ->method('remove')
            ->with('/aWebRoot/aCachePrefix/aFilterOne/aPathTwo');
        $filesystemMock
            ->expects($this->at(3))
            ->method('remove')
            ->with('/aWebRoot/aCachePrefix/aFilterTwo/aPathTwo');

        $resolver = new WebPathResolver(
            $filesystemMock,
            new RequestContext(),
            '/aWebRoot',
            'aCachePrefix'
        );

        $resolver->remove(
            ['aPathOne', 'aPathTwo'],
            ['aLoader'],
            ['aFilterOne', 'aFilterTwo']
        );
    }

    /**
     * @test
     */
    public function testRemoveCacheForFilterOnRemove()
    {
        $filesystemMock = $this->createFilesystemMock();
        $filesystemMock
            ->expects($this->once())
            ->method('remove')
            ->with([
                '/aWebRoot/aCachePrefix/aFilter',
            ]);

        $resolver = new WebPathResolver(
            $filesystemMock,
            new RequestContext(),
            '/aWebRoot',
            'aCachePrefix'
        );

        $resolver->remove([], ['aLoader'], ['aFilter']);
    }

    /**
     * @test
     */
    public function testRemoveCacheForSomeFiltersOnRemove()
    {
        $filesystemMock = $this->createFilesystemMock();
        $filesystemMock
            ->expects($this->once())
            ->method('remove')
            ->with([
                '/aWebRoot/aCachePrefix/aFilterOne',
                '/aWebRoot/aCachePrefix/aFilterTwo',
            ]);

        $resolver = new WebPathResolver(
            $filesystemMock,
            new RequestContext(),
            '/aWebRoot',
            'aCachePrefix'
        );

        $resolver->remove([], ['aLoader'], ['aFilterOne', 'aFilterTwo']);
    }

    /**
     * @test
     */
    public function testShouldRemoveDoubleSlashInUrl()
    {
        $resolver = new WebPathResolver(
            $this->createFilesystemMock(),
            new RequestContext(),
            '/aWebRoot',
            'aCachePrefix'
        );

        $rc = new \ReflectionClass($resolver);
        $method = $rc->getMethod('getFileUrl');
        $method->setAccessible(true);

        $result = $method->invokeArgs($resolver, ['/cats.jpg', 'some_filter']);

        $this->assertSame('aCachePrefix/some_filter/cats.jpg', $result);
    }

    /**
     * @test
     */
    public function testShouldSanitizeSeparatorBetweenSchemeAndAuthorityInUrl()
    {
        $resolver = new WebPathResolver(
            $this->createFilesystemMock(),
            new RequestContext(),
            '/aWebRoot',
            'aCachePrefix'
        );

        $rc = new \ReflectionClass($resolver);
        $method = $rc->getMethod('getFileUrl');
        $method->setAccessible(true);

        $result = $method->invokeArgs($resolver, ['https://some.meme.com/cute/cats.jpg', 'some_filter']);

        $this->assertSame('aCachePrefix/some_filter/https---some.meme.com/cute/cats.jpg', $result);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|Filesystem
     */
    protected function createFilesystemMock()
    {
        return $this->getMock('Symfony\Component\Filesystem\Filesystem');
    }
}
