<?php

namespace Anezi\ImagineBundle\Imagine\Cache\Resolver;

use Anezi\ImagineBundle\Binary\BinaryInterface;
use Symfony\Component\Routing\RequestContext;

/**
 * Class NoCacheWebPathResolver.
 */
class NoCacheWebPathResolver implements ResolverInterface
{
    /**
     * @var RequestContext
     */
    private $requestContext;

    /**
     * @param RequestContext $requestContext
     */
    public function __construct(RequestContext $requestContext)
    {
        $this->requestContext = $requestContext;
    }

    /**
     * {@inheritdoc}
     */
    public function isStored(string $path, string $loader, string $filter) : bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(string $path, string $loader, string $filter) : string
    {
        return sprintf('%s://%s/%s',
            $this->requestContext->getScheme(),
            $this->requestContext->getHost(),
            ltrim($path, '/')
        );
    }

    /**
     * {@inheritdoc}
     */
    public function store(BinaryInterface $binary, string $path, string $loader, string $filter)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function remove(array $paths, array $loaders, array $filters)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function fetch(string $path, string $loader, string $filter) : string
    {
        return file_get_contents($this->resolve($path, $loader, $filter));
    }
}
