<?php

namespace Anezi\ImagineBundle\Imagine\Cache\Resolver;

use Anezi\ImagineBundle\Binary\BinaryInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Routing\RequestContext;

/**
 * Class WebPathResolver.
 */
class WebPathResolver implements ResolverInterface
{
    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var RequestContext
     */
    protected $requestContext;

    /**
     * @var string
     */
    protected $webRoot;

    /**
     * @var string
     */
    protected $cachePrefix;

    /**
     * @var string
     */
    protected $cacheRoot;

    /**
     * @param Filesystem     $filesystem
     * @param RequestContext $requestContext
     * @param string         $webRootDir
     * @param string         $cachePrefix
     */
    public function __construct(
        Filesystem $filesystem,
        RequestContext $requestContext,
        $webRootDir,
        $cachePrefix = 'media/cache'
    ) {
        $this->filesystem = $filesystem;
        $this->requestContext = $requestContext;

        $this->webRoot = rtrim(str_replace('//', '/', $webRootDir), '/');
        $this->cachePrefix = ltrim(str_replace('//', '/', $cachePrefix), '/');
        $this->cacheRoot = $this->webRoot.'/'.$this->cachePrefix;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(string $path, string $loader, string $filter) : string
    {
        return sprintf('%s/%s',
            $this->getBaseUrl(),
            $this->getFileUrl($path, $loader, $filter)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function isStored(string $path, string $loader, string $filter) : bool
    {
        return is_file($this->getFilePath($path, $loader, $filter));
    }

    /**
     * {@inheritdoc}
     */
    public function store(BinaryInterface $binary, string $path, string $loader, string $filter)
    {
        $this->filesystem->dumpFile(
            $this->getFilePath($path, $loader, $filter),
            $binary->getContent()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function remove(array $paths, array $loaders, array $filters)
    {
        if (empty($paths) && empty($filters)) {
            return;
        }

        if (empty($paths)) {
            $filtersCacheDir = [];
            foreach ($filters as $filter) {
                $filtersCacheDir[] = $this->cacheRoot.'/'.$filter;
            }

            $this->filesystem->remove($filtersCacheDir);

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
     * {@inheritdoc}
     */
    protected function getFilePath(string $path, string $loader, string $filter) : string
    {
        return $this->webRoot.'/'.$this->getFileUrl($path, $loader, $filter);
    }

    /**
     * {@inheritdoc}
     */
    protected function getFileUrl(string $path, string $loader, string $filter) : string
    {
        // crude way of sanitizing URL scheme ("protocol") part
        $path = str_replace('://', '---', $path);

        return $this->cachePrefix.'/'.$loader.'/'.$filter.'/'.ltrim($path, '/');
    }

    /**
     * @return string
     */
    protected function getBaseUrl()
    {
        $port = '';
        if ('https' == $this->requestContext->getScheme() && $this->requestContext->getHttpsPort() != 443) {
            $port = ":{$this->requestContext->getHttpsPort()}";
        }

        if ('http' == $this->requestContext->getScheme() && $this->requestContext->getHttpPort() != 80) {
            $port = ":{$this->requestContext->getHttpPort()}";
        }

        $baseUrl = $this->requestContext->getBaseUrl();
        if ('.php' == substr($this->requestContext->getBaseUrl(), -4)) {
            $baseUrl = pathinfo($this->requestContext->getBaseUrl(), PATHINFO_DIRNAME);
        }
        $baseUrl = rtrim($baseUrl, '/\\');

        return sprintf('%s://%s%s%s',
            $this->requestContext->getScheme(),
            $this->requestContext->getHost(),
            $port,
            $baseUrl
        );
    }
}
