<?php

namespace Anezi\ImagineBundle\tests\Functional\Command;

use Anezi\ImagineBundle\Command\ResolveCacheCommand;
use Anezi\ImagineBundle\Tests\Functional\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @covers Anezi\ImagineBundle\Command\ResolveCacheCommand
 */
class ResolveCacheTest extends WebTestCase
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
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var string
     */
    protected $cacheRoot;

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
    public function testShouldResolveWithEmptyCache()
    {
        $this->assertFileNotExists($this->cacheRoot.'/thumbnail_web_path/images/cats.jpeg');

        $output = $this->executeConsole(
            new ResolveCacheCommand(),
            [
                'paths'     => ['images/cats.jpeg'],
                '--loaders' => ['web_path_loader'],
                '--filters' => ['thumbnail_web_path'],
            ]
        );

        $this->assertFileExists($this->cacheRoot.'/web_path_loader/thumbnail_web_path/images/cats.jpeg');
        $this->assertFileNotExists($this->cacheRoot.'/web_path_loader/thumbnail_default/images/cats.jpeg');
        $this->assertContains('http://localhost/images/web_path_loader/thumbnail_web_path/images/cats.jpeg', $output);
    }

    /**
     * @test
     */
    public function testShouldResolveWithCacheExists()
    {
        $this->filesystem->dumpFile(
            $this->cacheRoot.'/thumbnail_web_path/images/cats.jpeg',
            'anImageContent'
        );

        $output = $this->executeConsole(
            new ResolveCacheCommand(),
            [
                'paths'     => ['images/cats.jpeg'],
                '--loaders' => ['web_path_loader'],
                '--filters' => ['thumbnail_web_path'],
            ]
        );

        $this->assertFileExists($this->cacheRoot.'/web_path_loader/thumbnail_web_path/images/cats.jpeg');
        $this->assertFileNotExists($this->cacheRoot.'/web_path_loader/thumbnail_default/images/cats.jpeg');
        $this->assertContains('http://localhost/images/web_path_loader/thumbnail_web_path/images/cats.jpeg', $output);
    }

    /**
     * @test
     */
    public function testShouldResolveWithFewPathsAndSingleFilter()
    {
        $output = $this->executeConsole(
            new ResolveCacheCommand(),
            [
                'paths'     => ['images/cats.jpeg', 'images/cats2.jpeg'],
                '--loaders' => ['web_path_loader'],
                '--filters' => ['thumbnail_web_path'],
            ]
        );

        $this->assertContains('http://localhost/images/web_path_loader/thumbnail_web_path/images/cats.jpeg', $output);
        $this->assertContains('http://localhost/images/web_path_loader/thumbnail_web_path/images/cats2.jpeg', $output);
    }

    /**
     * @test
     */
    public function testShouldResolveWithFewPathsSingleFilterAndPartiallyFullCache()
    {
        $this->assertFileNotExists($this->cacheRoot.'/web_path_loader/thumbnail_web_path/images/cats.jpeg');

        $this->filesystem->dumpFile(
            $this->cacheRoot.'/web_path_loader/thumbnail_web_path/images/cats2.jpeg',
            'anImageContent'
        );

        $output = $this->executeConsole(
            new ResolveCacheCommand(),
            [
                'paths'     => ['images/cats.jpeg', 'images/cats2.jpeg'],
                '--loaders' => ['web_path_loader'],
                '--filters' => ['thumbnail_web_path'],
            ]
        );

        $this->assertFileNotExists($this->cacheRoot.'/web_path_loader/thumbnail_default/images/cats.jpeg');
        $this->assertFileExists($this->cacheRoot.'/web_path_loader/thumbnail_web_path/images/cats.jpeg');
        $this->assertFileExists($this->cacheRoot.'/web_path_loader/thumbnail_web_path/images/cats2.jpeg');
        $this->assertContains('http://localhost/images/web_path_loader/thumbnail_web_path/images/cats.jpeg', $output);
        $this->assertContains('http://localhost/images/web_path_loader/thumbnail_web_path/images/cats2.jpeg', $output);
    }

    /**
     * @test
     */
    public function testShouldResolveWithFewPathsAndFewFilters()
    {
        $output = $this->executeConsole(
            new ResolveCacheCommand(),
            [
                'paths'     => ['images/cats.jpeg', 'images/cats2.jpeg'],
                '--loaders' => ['web_path_loader'],
                '--filters' => ['thumbnail_web_path', 'thumbnail_default'],
            ]
        );

        $this->assertContains('http://localhost/images/web_path_loader/thumbnail_web_path/images/cats.jpeg', $output);
        $this->assertContains('http://localhost/images/web_path_loader/thumbnail_web_path/images/cats2.jpeg', $output);
        $this->assertContains('http://localhost/images/web_path_loader/thumbnail_default/images/cats.jpeg', $output);
        $this->assertContains('http://localhost/images/web_path_loader/thumbnail_default/images/cats2.jpeg', $output);
    }

    /**
     * @test
     */
    public function testShouldResolveWithFewPathsAndWithoutFilters()
    {
        $output = $this->executeConsole(
            new ResolveCacheCommand(),
            ['paths' => ['images/cats.jpeg', 'images/cats2.jpeg']]
        );

        $this->assertContains('http://localhost/images/web_path_loader/thumbnail_web_path/images/cats.jpeg', $output);
        $this->assertContains('http://localhost/images/web_path_loader/thumbnail_web_path/images/cats2.jpeg', $output);
        $this->assertContains('http://localhost/images/web_path_loader/thumbnail_default/images/cats.jpeg', $output);
        $this->assertContains('http://localhost/images/web_path_loader/thumbnail_default/images/cats2.jpeg', $output);
    }

    /**
     * Helper function return the result of command execution.
     *
     * @param Command $command
     * @param array   $arguments
     * @param array   $options
     *
     * @return string
     */
    protected function executeConsole(Command $command, array $arguments = [], array $options = [])
    {
        $command->setApplication(new Application($this->createClient()->getKernel()));
        if ($command instanceof ContainerAwareCommand) {
            $command->setContainer($this->createClient()->getContainer());
        }

        $arguments = array_replace(['command' => $command->getName()], $arguments);
        $options = array_replace(['--env' => 'test'], $options);

        $commandTester = new CommandTester($command);
        $commandTester->execute($arguments, $options);

        return $commandTester->getDisplay();
    }
}
