<?php

namespace Anezi\ImagineBundle\tests\Functional\Controller;

use Anezi\ImagineBundle\Imagine\Cache\Signer;
use Anezi\ImagineBundle\Tests\Functional\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Response;

/**
 * @covers Anezi\ImagineBundle\Controller\ImagineController
 */
class ImagineControllerTest extends WebTestCase
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * @var string
     */
    protected $webRoot;

    /**
     * @var string
     */
    protected $cacheRoot;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        parent::setUp();

        $this->client = $this->createClient();

        $this->webRoot = self::$kernel->getContainer()->getParameter('kernel.root_dir').'/web';
        $this->cacheRoot = $this->webRoot.'/images';

        $this->filesystem = new Filesystem();
        $this->filesystem->remove($this->cacheRoot);
    }

    /**
     * @test
     */
    public function testShouldResolveFromCache()
    {
        $this->filesystem->dumpFile(
            $this->cacheRoot.'/default/thumbnail_web_path/images/cats.jpeg',
            'anImageContent'
        );

        $this->client->request('GET', '/images/default/thumbnail_web_path/images/cats.jpeg');

        $response = $this->client->getResponse();

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $response);
        $this->assertSame(200, $response->getStatusCode());

        $this->assertFileExists($this->cacheRoot.'/default/thumbnail_web_path/images/cats.jpeg');
    }

    /**
     * @expectedException \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @expectedExceptionMessage Source image could not be found
     */
    public function testShouldThrowNotFoundHttpExceptionIfFileNotExists()
    {
        $this->client->request('GET', '/images/default/thumbnail_web_path/images/shrodinger_cats_which_not_exist.jpeg');
    }

    /**
     * @expectedException \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function testInvalidFilterShouldThrowNotFoundHttpException()
    {
        $this->client->request('GET', '/images/default/invalid-filter/images/cats.jpeg');
    }

    /**
     * @test
     */
    public function testShouldResolveWithCustomFiltersFromCache()
    {
        /** @var Signer $signer */
        $signer = self::$kernel->getContainer()->get('anezi_imagine.cache.signer');

        $params = [
            'filters' => [
                'thumbnail' => ['size' => [50, 50]],
            ],
        ];

        $path = 'images/cats.jpeg';

        $hash = $signer->sign($path, $params['filters']);

        $expectedCachePath = 'thumbnail_web_path/rc/'.$hash.'/'.$path;

        $url = 'http://localhost/images/storage/'.$expectedCachePath.'?'.http_build_query($params);

        $this->filesystem->dumpFile(
            $this->cacheRoot.'/storage/'.$expectedCachePath,
            'anImageContent'
        );

        $this->client->request('GET', $url);

        $response = $this->client->getResponse();

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame(200, $response->getStatusCode());

        $this->assertFileExists($this->cacheRoot.'/storage/'.$expectedCachePath);
    }

    /**
     * @test
     */
    public function testShouldResolvePathWithSpecialCharactersAndWhiteSpaces()
    {
        $this->filesystem->dumpFile(
            $this->cacheRoot.'/storage/thumbnail_web_path/foo bar.jpeg',
            'anImageContent'
        );

        $this->client->request('GET', '/images/storage/thumbnail_web_path/foo+bar.jpeg');

        $response = $this->client->getResponse();

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame(200, $response->getStatusCode());

        $this->assertFileExists($this->cacheRoot.'/storage/thumbnail_web_path/foo bar.jpeg');
    }
}
