<?php

namespace Anezi\ImagineBundle\Imagine\Cache\Resolver;

use Anezi\ImagineBundle\Binary\BinaryInterface;

/**
 * ProxyResolver.
 *
 * @author Robert Schönthal <robert.schoenthal@gmail.com>
 */
class ProxyResolver implements ResolverInterface
{
    /**
     * @var ResolverInterface
     */
    protected $resolver;

    /**
     * a list of proxy hosts (picks a random one for each generation to seed browser requests among multiple hosts).
     *
     * @var array
     */
    protected $hosts = [];

    /**
     * @param ResolverInterface $resolver
     * @param string[]          $hosts
     */
    public function __construct(ResolverInterface $resolver, array $hosts)
    {
        $this->resolver = $resolver;
        $this->hosts = $hosts;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(string $path, string $loader, string $filter) : string
    {
        return $this->rewriteUrl($this->resolver->resolve($path, $loader, $filter));
    }

    /**
     * {@inheritdoc}
     */
    public function store(BinaryInterface $binary, string $path, string $loader, string $filter)
    {
        return $this->resolver->store($binary, $path, $loader, $filter);
    }

    /**
     * {@inheritdoc}
     */
    public function isStored(string $path, string $loader, string $filter) : bool
    {
        return $this->resolver->isStored($path, $loader, $filter);
    }

    /**
     * {@inheritdoc}
     */
    public function remove(array $paths, array $loaders, array $filters)
    {
        return $this->resolver->remove($paths, $loaders, $filters);
    }

    /**
     * @param $url
     *
     * @return string
     */
    protected function rewriteUrl($url)
    {
        if (empty($this->hosts)) {
            return $url;
        }

        $randKey = array_rand($this->hosts, 1);

        // BC
        if (is_numeric($randKey)) {
            $host = parse_url($url, PHP_URL_SCHEME).'://'.parse_url($url, PHP_URL_HOST);
            $proxyHost = $this->hosts[$randKey];

            return str_replace($host, $proxyHost, $url);
        }

        if (0 === strpos($randKey, 'regexp/')) {
            $regExp = substr($randKey, 6);

            return preg_replace($regExp, $this->hosts[$randKey], $url);
        }

        return str_replace($randKey, $this->hosts[$randKey], $url);
    }

    /**
     * {@inheritdoc}
     */
    public function fetch(string $path, string $loader, string $filter) : string
    {
        return $this->resolver->fetch($path, $loader, $filter);
    }
}
