<?php

namespace Anezi\ImagineBundle;

use Anezi\ImagineBundle\DependencyInjection\Compiler\FiltersCompilerPass;
use Anezi\ImagineBundle\DependencyInjection\Compiler\LoadersCompilerPass;
use Anezi\ImagineBundle\DependencyInjection\Compiler\PostProcessorsCompilerPass;
use Anezi\ImagineBundle\DependencyInjection\Compiler\ResolversCompilerPass;
use Anezi\ImagineBundle\DependencyInjection\Factory\Loader\FileSystemLoaderFactory;
use Anezi\ImagineBundle\DependencyInjection\Factory\Loader\StreamLoaderFactory;
use Anezi\ImagineBundle\DependencyInjection\Factory\Loader\FlysystemLoaderFactory;
use Anezi\ImagineBundle\DependencyInjection\Factory\Resolver\AwsS3ResolverFactory;
use Anezi\ImagineBundle\DependencyInjection\Factory\Resolver\WebPathResolverFactory;
use Anezi\ImagineBundle\DependencyInjection\AneziImagineExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class AneziImagineBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new LoadersCompilerPass());
        $container->addCompilerPass(new FiltersCompilerPass());
        $container->addCompilerPass(new PostProcessorsCompilerPass());
        $container->addCompilerPass(new ResolversCompilerPass());

        /** @var $extension AneziImagineExtension */
        $extension = $container->getExtension('anezi_imagine');

        $extension->addResolverFactory(new WebPathResolverFactory());
        $extension->addResolverFactory(new AwsS3ResolverFactory());

        $extension->addLoaderFactory(new StreamLoaderFactory());
        $extension->addLoaderFactory(new FileSystemLoaderFactory());
        $extension->addLoaderFactory(new FlysystemLoaderFactory());
    }
}
