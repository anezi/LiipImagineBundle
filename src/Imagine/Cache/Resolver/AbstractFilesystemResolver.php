<?php

namespace Anezi\ImagineBundle\Imagine\Cache\Resolver;

use Anezi\ImagineBundle\Binary\BinaryInterface;
use Anezi\ImagineBundle\Imagine\Cache\CacheManager;
use Anezi\ImagineBundle\Imagine\Cache\CacheManagerAwareInterface;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class AbstractFilesystemResolver.
 */
abstract class AbstractFilesystemResolver implements ResolverInterface, CacheManagerAwareInterface
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var string
     */
    protected $basePath = '';

    /**
     * @var CacheManager
     */
    protected $cacheManager;

    /**
     * @var int
     */
    protected $folderPermissions = 0777;

    /**
     * Constructs a filesystem based cache resolver.
     *
     * @param Filesystem $filesystem
     */
    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    /**
     * @param Request $request
     */
    public function setRequest(Request $request = null)
    {
        $this->request = $request;
    }

    /**
     * @param CacheManager $cacheManager
     */
    public function setCacheManager(CacheManager $cacheManager)
    {
        $this->cacheManager = $cacheManager;
    }

    /**
     * Set the base path to.
     *
     * @param $basePath
     */
    public function setBasePath($basePath)
    {
        $this->basePath = $basePath;
    }

    /**
     * @param int $folderPermissions
     */
    public function setFolderPermissions($folderPermissions)
    {
        $this->folderPermissions = $folderPermissions;
    }

    /**
     * {@inheritdoc}
     */
    public function isStored(string $path, string $loader, string $filter)
    {
        return file_exists($this->getFilePath($path, $loader, $filter));
    }

    /**
     * {@inheritdoc}
     */
    public function store(BinaryInterface $binary, string $path, string $loader, string $filter)
    {
        $filePath = $this->getFilePath($path, $loader, $filter);

        $dir = pathinfo($filePath, PATHINFO_DIRNAME);

        $this->makeFolder($dir);

        file_put_contents($filePath, $binary->getContent());
    }

    /**
     * {@inheritdoc}
     */
    public function remove(array $paths, array $loaders, array $filters)
    {
        if (empty($paths) && empty($filters)) {
            return;
        }

        // TODO: this logic has to be refactored.
        list($rootCachePath) = explode(current($filters), $this->getFilePath('whateverpath', current($loaders), current($filters)));

        if (empty($paths)) {
            $filtersCachePaths = [];
            foreach ($filters as $filter) {
                $filterCachePath = $rootCachePath.$filter;
                if (is_dir($filterCachePath)) {
                    $filtersCachePaths[] = $filterCachePath;
                }
            }

            $this->filesystem->remove($filtersCachePaths);

            return;
        }

        foreach ($paths as $path) {
            foreach ($loaders as $loader) {
                foreach ($filters as $filter) {
                    $this->filesystem->remove($this->getFilePath($path, $loader, $filter));
                }
            }
        }
    }

    /**
     * @return Request
     *
     * @throws \LogicException
     */
    protected function getRequest()
    {
        if (false == $this->request) {
            throw new \LogicException('The request was not injected, inject it before using resolver.');
        }

        return $this->request;
    }

    /**
     * @param string $dir
     *
     * @throws \RuntimeException
     */
    protected function makeFolder($dir)
    {
        if (!is_dir($dir)) {
            $parent = dirname($dir);
            try {
                $this->makeFolder($parent);
                $this->filesystem->mkdir($dir);
                $this->filesystem->chmod($dir, $this->folderPermissions);
            } catch (IOException $e) {
                throw new \RuntimeException(sprintf('Could not create directory %s', $dir), 0, $e);
            }
        }
    }

    /**
     * Return the local file path.
     *
     *
     * @param string $path   The resource path to convert.
     * @param string $loader
     * @param string $filter The name of the imagine filter.
     *
     * @return string
     */
    abstract protected function getFilePath(string $path, string $loader, string $filter) : string;
}
