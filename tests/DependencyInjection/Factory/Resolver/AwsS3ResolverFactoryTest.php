<?php

namespace Anezi\ImagineBundle\tests\DependencyInjection\Factory\Resolver;

use Anezi\ImagineBundle\DependencyInjection\Factory\Resolver\AwsS3ResolverFactory;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;

/**
 * @covers Anezi\ImagineBundle\DependencyInjection\Factory\Resolver\AwsS3ResolverFactory<extended>
 */
class AwsS3ResolverFactoryTest extends \Phpunit_Framework_TestCase
{
    public function testImplementsResolverFactoryInterface()
    {
        $rc = new \ReflectionClass('Anezi\ImagineBundle\DependencyInjection\Factory\Resolver\AwsS3ResolverFactory');

        $this->assertTrue($rc->implementsInterface('Anezi\ImagineBundle\DependencyInjection\Factory\Resolver\ResolverFactoryInterface'));
    }

    public function testCouldBeConstructedWithoutAnyArguments()
    {
        new AwsS3ResolverFactory();
    }

    public function testReturnExpectedName()
    {
        $resolver = new AwsS3ResolverFactory();

        $this->assertSame('aws_s3', $resolver->getName());
    }

    public function testCreateResolverDefinitionOnCreate()
    {
        $container = new ContainerBuilder();

        $resolver = new AwsS3ResolverFactory();

        $resolver->create($container, 'theResolverName', [
            'client_config' => [],
            'bucket'        => 'theBucket',
            'acl'           => 'theAcl',
            'url_options'   => ['fooKey' => 'fooVal'],
            'get_options'   => [],
            'put_options'   => ['barKey' => 'barVal'],
            'cache'         => false,
            'proxies'       => [],
        ]);

        $this->assertTrue($container->hasDefinition('anezi_imagine.cache.resolver.theresolvername'));

        $resolverDefinition = $container->getDefinition('anezi_imagine.cache.resolver.theresolvername');
        $this->assertInstanceOf('Symfony\Component\DependencyInjection\DefinitionDecorator', $resolverDefinition);
        $this->assertSame('anezi_imagine.cache.resolver.prototype.aws_s3', $resolverDefinition->getParent());

        $this->assertInstanceOf('Symfony\Component\DependencyInjection\Reference', $resolverDefinition->getArgument(0));
        $this->assertSame('anezi_imagine.cache.resolver.theresolvername.client', $resolverDefinition->getArgument(0));

        $this->assertSame('theBucket', $resolverDefinition->getArgument(1));
        $this->assertSame('theAcl', $resolverDefinition->getArgument(2));
        $this->assertSame(['fooKey' => 'fooVal'], $resolverDefinition->getArgument(3));
        $this->assertSame(['barKey' => 'barVal'], $resolverDefinition->getArgument(4));
    }

    public function testOverrideDeprecatedUrlOptionsWithNewGetOptions()
    {
        $container = new ContainerBuilder();

        $resolver = new AwsS3ResolverFactory();

        $resolver->create($container, 'theResolverName', [
            'client_config' => [],
            'bucket'        => 'theBucket',
            'acl'           => 'theAcl',
            'url_options'   => ['fooKey' => 'fooVal', 'barKey' => 'barVal'],
            'get_options'   => ['fooKey' => 'fooVal_overridden'],
            'put_options'   => [],
            'cache'         => false,
            'proxies'       => [],
        ]);

        $this->assertTrue($container->hasDefinition('anezi_imagine.cache.resolver.theresolvername'));

        $resolverDefinition = $container->getDefinition('anezi_imagine.cache.resolver.theresolvername');
        $this->assertSame(['fooKey' => 'fooVal_overridden', 'barKey' => 'barVal'], $resolverDefinition->getArgument(3));
    }

    public function testCreateS3ClientDefinitionOnCreate()
    {
        $container = new ContainerBuilder();

        $resolver = new AwsS3ResolverFactory();

        $resolver->create($container, 'theResolverName', [
            'client_config' => ['theClientConfigKey' => 'theClientConfigVal'],
            'bucket'        => 'aBucket',
            'acl'           => 'aAcl',
            'url_options'   => [],
            'get_options'   => [],
            'put_options'   => [],
            'cache'         => false,
            'proxies'       => [],
        ]);

        $this->assertTrue($container->hasDefinition('anezi_imagine.cache.resolver.theresolvername.client'));

        $clientDefinition = $container->getDefinition('anezi_imagine.cache.resolver.theresolvername.client');
        $this->assertSame('Aws\S3\S3Client', $clientDefinition->getClass());
        $this->assertSame(['theClientConfigKey' => 'theClientConfigVal'], $clientDefinition->getArgument(0));
    }

    public function testCreateS3ClientDefinitionWithFactoryOnCreate()
    {
        if (version_compare(Kernel::VERSION_ID, '20600') < 0) {
            $this->markTestSkipped('No need to test on symfony < 2.6');
        }

        $container = new ContainerBuilder();

        $resolver = new AwsS3ResolverFactory();

        $resolver->create($container, 'theResolverName', [
            'client_config' => ['theClientConfigKey' => 'theClientConfigVal'],
            'bucket'        => 'aBucket',
            'acl'           => 'aAcl',
            'url_options'   => [],
            'get_options'   => [],
            'put_options'   => [],
            'cache'         => false,
            'proxies'       => [],
        ]);

        $clientDefinition = $container->getDefinition('anezi_imagine.cache.resolver.theresolvername.client');
        $this->assertSame(['Aws\S3\S3Client', 'factory'], $clientDefinition->getFactory());
    }

    public function testLegacyCreateS3ClientDefinitionWithFactoryOnCreate()
    {
        if (version_compare(Kernel::VERSION_ID, '20600') >= 0) {
            $this->markTestSkipped('No need to test on symfony >= 2.6');
        }

        $container = new ContainerBuilder();

        $resolver = new AwsS3ResolverFactory();

        $resolver->create($container, 'theResolverName', [
            'client_config' => ['theClientConfigKey' => 'theClientConfigVal'],
            'bucket'        => 'aBucket',
            'acl'           => 'aAcl',
            'url_options'   => [],
            'get_options'   => [],
            'put_options'   => [],
            'cache'         => false,
            'proxies'       => [],
        ]);

        $clientDefinition = $container->getDefinition('anezi_imagine.cache.resolver.theresolvername.client');
        $this->assertSame('Aws\S3\S3Client', $clientDefinition->getFactoryClass());
        $this->assertSame('factory', $clientDefinition->getFactoryMethod());
    }

    public function testWrapResolverWithProxyOnCreateWithoutCache()
    {
        $container = new ContainerBuilder();

        $resolver = new AwsS3ResolverFactory();

        $resolver->create($container, 'theResolverName', [
            'client_config' => [],
            'bucket'        => 'aBucket',
            'acl'           => 'aAcl',
            'url_options'   => [],
            'get_options'   => [],
            'put_options'   => [],
            'cache'         => false,
            'proxies'       => ['foo'],
        ]);

        $this->assertTrue($container->hasDefinition('anezi_imagine.cache.resolver.theresolvername.proxied'));
        $proxiedResolverDefinition = $container->getDefinition('anezi_imagine.cache.resolver.theresolvername.proxied');
        $this->assertInstanceOf('Symfony\Component\DependencyInjection\DefinitionDecorator', $proxiedResolverDefinition);
        $this->assertSame('anezi_imagine.cache.resolver.prototype.aws_s3', $proxiedResolverDefinition->getParent());

        $this->assertFalse($container->hasDefinition('anezi_imagine.cache.resolver.theresolvername.cached'));

        $this->assertTrue($container->hasDefinition('anezi_imagine.cache.resolver.theresolvername'));
        $resolverDefinition = $container->getDefinition('anezi_imagine.cache.resolver.theresolvername');
        $this->assertInstanceOf('Symfony\Component\DependencyInjection\DefinitionDecorator', $resolverDefinition);
        $this->assertSame('anezi_imagine.cache.resolver.prototype.proxy', $resolverDefinition->getParent());

        $this->assertInstanceOf('Symfony\Component\DependencyInjection\Reference', $resolverDefinition->getArgument(0));
        $this->assertSame('anezi_imagine.cache.resolver.theresolvername.proxied', $resolverDefinition->getArgument(0));

        $this->assertSame(['foo'], $resolverDefinition->getArgument(1));
    }

    public function testWrapResolverWithCacheOnCreateWithoutProxy()
    {
        $container = new ContainerBuilder();

        $resolver = new AwsS3ResolverFactory();

        $resolver->create($container, 'theResolverName', [
            'client_config' => [],
            'bucket'        => 'aBucket',
            'acl'           => 'aAcl',
            'url_options'   => [],
            'get_options'   => [],
            'put_options'   => [],
            'cache'         => 'theCacheServiceId',
            'proxies'       => [],
        ]);

        $this->assertTrue($container->hasDefinition('anezi_imagine.cache.resolver.theresolvername.cached'));
        $cachedResolverDefinition = $container->getDefinition('anezi_imagine.cache.resolver.theresolvername.cached');
        $this->assertInstanceOf('Symfony\Component\DependencyInjection\DefinitionDecorator', $cachedResolverDefinition);
        $this->assertSame('anezi_imagine.cache.resolver.prototype.aws_s3', $cachedResolverDefinition->getParent());

        $this->assertFalse($container->hasDefinition('anezi_imagine.cache.resolver.theresolvername.proxied'));

        $this->assertTrue($container->hasDefinition('anezi_imagine.cache.resolver.theresolvername'));
        $resolverDefinition = $container->getDefinition('anezi_imagine.cache.resolver.theresolvername');
        $this->assertInstanceOf('Symfony\Component\DependencyInjection\DefinitionDecorator', $resolverDefinition);
        $this->assertSame('anezi_imagine.cache.resolver.prototype.cache', $resolverDefinition->getParent());

        $this->assertInstanceOf('Symfony\Component\DependencyInjection\Reference', $resolverDefinition->getArgument(0));
        $this->assertSame('thecacheserviceid', $resolverDefinition->getArgument(0));

        $this->assertInstanceOf('Symfony\Component\DependencyInjection\Reference', $resolverDefinition->getArgument(1));
        $this->assertSame('anezi_imagine.cache.resolver.theresolvername.cached', $resolverDefinition->getArgument(1));
    }

    public function testWrapResolverWithProxyAndCacheOnCreate()
    {
        $container = new ContainerBuilder();

        $resolver = new AwsS3ResolverFactory();

        $resolver->create($container, 'theResolverName', [
            'client_config' => [],
            'bucket'        => 'aBucket',
            'acl'           => 'aAcl',
            'url_options'   => [],
            'get_options'   => [],
            'put_options'   => [],
            'cache'         => 'theCacheServiceId',
            'proxies'       => ['foo'],
        ]);

        $this->assertTrue($container->hasDefinition('anezi_imagine.cache.resolver.theresolvername.proxied'));
        $proxiedResolverDefinition = $container->getDefinition('anezi_imagine.cache.resolver.theresolvername.proxied');
        $this->assertInstanceOf('Symfony\Component\DependencyInjection\DefinitionDecorator', $proxiedResolverDefinition);
        $this->assertSame('anezi_imagine.cache.resolver.prototype.aws_s3', $proxiedResolverDefinition->getParent());

        $this->assertTrue($container->hasDefinition('anezi_imagine.cache.resolver.theresolvername.cached'));
        $cachedResolverDefinition = $container->getDefinition('anezi_imagine.cache.resolver.theresolvername.cached');
        $this->assertInstanceOf('Symfony\Component\DependencyInjection\DefinitionDecorator', $cachedResolverDefinition);
        $this->assertSame('anezi_imagine.cache.resolver.prototype.proxy', $cachedResolverDefinition->getParent());

        $this->assertInstanceOf('Symfony\Component\DependencyInjection\Reference', $cachedResolverDefinition->getArgument(0));
        $this->assertSame('anezi_imagine.cache.resolver.theresolvername.proxied', $cachedResolverDefinition->getArgument(0));

        $this->assertSame(['foo'], $cachedResolverDefinition->getArgument(1));

        $this->assertTrue($container->hasDefinition('anezi_imagine.cache.resolver.theresolvername'));
        $resolverDefinition = $container->getDefinition('anezi_imagine.cache.resolver.theresolvername');
        $this->assertInstanceOf('Symfony\Component\DependencyInjection\DefinitionDecorator', $resolverDefinition);
        $this->assertSame('anezi_imagine.cache.resolver.prototype.cache', $resolverDefinition->getParent());

        $this->assertInstanceOf('Symfony\Component\DependencyInjection\Reference', $resolverDefinition->getArgument(0));
        $this->assertSame('thecacheserviceid', $resolverDefinition->getArgument(0));

        $this->assertInstanceOf('Symfony\Component\DependencyInjection\Reference', $resolverDefinition->getArgument(1));
        $this->assertSame('anezi_imagine.cache.resolver.theresolvername.cached', $resolverDefinition->getArgument(1));
    }

    public function testWrapResolverWithProxyMatchReplaceStrategyOnCreate()
    {
        $container = new ContainerBuilder();

        $resolver = new AwsS3ResolverFactory();

        $resolver->create($container, 'theResolverName', [
            'client_config' => [],
            'bucket'        => 'aBucket',
            'acl'           => 'aAcl',
            'url_options'   => [],
            'get_options'   => [],
            'put_options'   => [],
            'cache'         => 'theCacheServiceId',
            'proxies'       => ['foo' => 'bar'],
        ]);

        $this->assertTrue($container->hasDefinition('anezi_imagine.cache.resolver.theresolvername.proxied'));
        $proxiedResolverDefinition = $container->getDefinition('anezi_imagine.cache.resolver.theresolvername.proxied');
        $this->assertInstanceOf('Symfony\Component\DependencyInjection\DefinitionDecorator', $proxiedResolverDefinition);
        $this->assertSame('anezi_imagine.cache.resolver.prototype.aws_s3', $proxiedResolverDefinition->getParent());

        $this->assertTrue($container->hasDefinition('anezi_imagine.cache.resolver.theresolvername.cached'));
        $cachedResolverDefinition = $container->getDefinition('anezi_imagine.cache.resolver.theresolvername.cached');
        $this->assertInstanceOf('Symfony\Component\DependencyInjection\DefinitionDecorator', $cachedResolverDefinition);
        $this->assertSame('anezi_imagine.cache.resolver.prototype.proxy', $cachedResolverDefinition->getParent());

        $this->assertInstanceOf('Symfony\Component\DependencyInjection\Reference', $cachedResolverDefinition->getArgument(0));
        $this->assertSame('anezi_imagine.cache.resolver.theresolvername.proxied', $cachedResolverDefinition->getArgument(0));

        $this->assertSame(['foo' => 'bar'], $cachedResolverDefinition->getArgument(1));
    }

    public function testSetCachePrefixIfDefined()
    {
        $container = new ContainerBuilder();

        $resolver = new AwsS3ResolverFactory();

        $resolver->create($container, 'theResolverName', [
            'client_config' => [],
            'bucket'        => 'aBucket',
            'acl'           => 'aAcl',
            'url_options'   => [],
            'get_options'   => [],
            'put_options'   => [],
            'cache_prefix'  => 'theCachePrefix',
            'cache'         => null,
            'proxies'       => [],
        ]);

        $this->assertTrue($container->hasDefinition('anezi_imagine.cache.resolver.theresolvername'));
        $resolverDefinition = $container->getDefinition('anezi_imagine.cache.resolver.theresolvername');

        $methodCalls = $resolverDefinition->getMethodCalls();

        $this->assertCount(1, $methodCalls);
        $this->assertSame('setCachePrefix', $methodCalls[0][0]);
        $this->assertSame(['theCachePrefix'], $methodCalls[0][1]);
    }

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage The child node "bucket" at path "aws_s3" must be configured.
     */
    public function testThrowBucketNotSetOnAddConfiguration()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('aws_s3', 'array');

        $resolver = new AwsS3ResolverFactory();
        $resolver->addConfiguration($rootNode);

        $this->processConfigTree($treeBuilder, []);
    }

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage The child node "client_config" at path "aws_s3" must be configured.
     */
    public function testThrowClientConfigNotSetOnAddConfiguration()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('aws_s3', 'array');

        $resolver = new AwsS3ResolverFactory();
        $resolver->addConfiguration($rootNode);

        $this->processConfigTree($treeBuilder, [
            'aws_s3' => [
                'bucket' => 'aBucket',
            ],
        ]);
    }

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage Invalid type for path "aws_s3.client_config". Expected array, but got string
     */
    public function testThrowClientConfigNotArrayOnAddConfiguration()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('aws_s3', 'array');

        $resolver = new AwsS3ResolverFactory();
        $resolver->addConfiguration($rootNode);

        $this->processConfigTree($treeBuilder, [
            'aws_s3' => [
                'bucket'        => 'aBucket',
                'client_config' => 'not_array',
            ],
        ]);
    }

    public function testProcessCorrectlyOptionsOnAddConfiguration()
    {
        $expectedClientConfig = [
            'theKey'      => 'theClientConfigVal',
            'theOtherKey' => 'theOtherClientConfigValue',
        ];
        $expectedUrlOptions = [
            'theKey'      => 'theUrlOptionsVal',
            'theOtherKey' => 'theOtherUrlOptionsValue',
        ];
        $expectedGetOptions = [
            'theKey'      => 'theGetOptionsVal',
            'theOtherKey' => 'theOtherGetOptionsValue',
        ];
        $expectedObjectOptions = [
            'theKey'      => 'theObjectOptionsVal',
            'theOtherKey' => 'theOtherObjectOptionsValue',
        ];
        $expectedBucket = 'theBucket';
        $expectedAcl = 'theAcl';
        $expectedCachePrefix = 'theCachePrefix';

        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('aws_s3', 'array');

        $resolver = new AwsS3ResolverFactory();
        $resolver->addConfiguration($rootNode);

        $config = $this->processConfigTree($treeBuilder, [
            'aws_s3' => [
                'bucket'        => $expectedBucket,
                'acl'           => $expectedAcl,
                'client_config' => $expectedClientConfig,
                'url_options'   => $expectedUrlOptions,
                'get_options'   => $expectedGetOptions,
                'put_options'   => $expectedObjectOptions,
                'cache_prefix'  => $expectedCachePrefix,
            ],
        ]);

        $this->assertArrayHasKey('bucket', $config);
        $this->assertSame($expectedBucket, $config['bucket']);

        $this->assertArrayHasKey('acl', $config);
        $this->assertSame($expectedAcl, $config['acl']);

        $this->assertArrayHasKey('client_config', $config);
        $this->assertSame($expectedClientConfig, $config['client_config']);

        $this->assertArrayHasKey('url_options', $config);
        $this->assertSame($expectedUrlOptions, $config['url_options']);

        $this->assertArrayHasKey('get_options', $config);
        $this->assertSame($expectedGetOptions, $config['get_options']);

        $this->assertArrayHasKey('put_options', $config);
        $this->assertSame($expectedObjectOptions, $config['put_options']);

        $this->assertArrayHasKey('cache_prefix', $config);
        $this->assertSame($expectedCachePrefix, $config['cache_prefix']);
    }

    public function testAddDefaultOptionsIfNotSetOnAddConfiguration()
    {
        $expectedAcl = 'public-read';

        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('aws_s3', 'array');

        $resolver = new AwsS3ResolverFactory();
        $resolver->addConfiguration($rootNode);

        $config = $this->processConfigTree($treeBuilder, [
            'aws_s3' => [
                'bucket'        => 'aBucket',
                'client_config' => [],
            ],
        ]);

        $this->assertArrayHasKey('acl', $config);
        $this->assertSame($expectedAcl, $config['acl']);

        $this->assertArrayHasKey('url_options', $config);
        $this->assertSame([], $config['url_options']);

        $this->assertArrayHasKey('get_options', $config);
        $this->assertSame([], $config['get_options']);

        $this->assertArrayHasKey('cache_prefix', $config);
        $this->assertNull($config['cache_prefix']);
    }

    public function testSupportAwsV3ClientConfig()
    {
        $expectedClientConfig = [
            'credentials' => [
                'key'    => 'theKey',
                'secret' => 'theSecret',
                'token'  => 'theToken',
            ],
            'region'  => 'theRegion',
            'version' => 'theVersion',
        ];
        $expectedUrlOptions = [
            'theKey'      => 'theUrlOptionsVal',
            'theOtherKey' => 'theOtherUrlOptionsValue',
        ];
        $expectedGetOptions = [
            'theKey'      => 'theGetOptionsVal',
            'theOtherKey' => 'theOtherGetOptionsValue',
        ];
        $expectedObjectOptions = [
            'theKey'      => 'theObjectOptionsVal',
            'theOtherKey' => 'theOtherObjectOptionsValue',
        ];
        $expectedBucket = 'theBucket';
        $expectedAcl = 'theAcl';
        $expectedCachePrefix = 'theCachePrefix';

        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('aws_s3', 'array');

        $resolver = new AwsS3ResolverFactory();
        $resolver->addConfiguration($rootNode);

        $config = $this->processConfigTree($treeBuilder, [
            'aws_s3' => [
                'bucket'        => $expectedBucket,
                'acl'           => $expectedAcl,
                'client_config' => $expectedClientConfig,
                'url_options'   => $expectedUrlOptions,
                'get_options'   => $expectedGetOptions,
                'put_options'   => $expectedObjectOptions,
                'cache_prefix'  => $expectedCachePrefix,
            ],
        ]);

        $this->assertArrayHasKey('bucket', $config);
        $this->assertSame($expectedBucket, $config['bucket']);

        $this->assertArrayHasKey('acl', $config);
        $this->assertSame($expectedAcl, $config['acl']);

        $this->assertArrayHasKey('client_config', $config);
        $this->assertSame($expectedClientConfig, $config['client_config']);

        $this->assertArrayHasKey('url_options', $config);
        $this->assertSame($expectedUrlOptions, $config['url_options']);

        $this->assertArrayHasKey('get_options', $config);
        $this->assertSame($expectedGetOptions, $config['get_options']);

        $this->assertArrayHasKey('put_options', $config);
        $this->assertSame($expectedObjectOptions, $config['put_options']);

        $this->assertArrayHasKey('cache_prefix', $config);
        $this->assertSame($expectedCachePrefix, $config['cache_prefix']);
    }

    /**
     * @param TreeBuilder $treeBuilder
     * @param array       $configs
     *
     * @return array
     */
    protected function processConfigTree(TreeBuilder $treeBuilder, array $configs)
    {
        $processor = new Processor();

        return $processor->process($treeBuilder->buildTree(), $configs);
    }
}
