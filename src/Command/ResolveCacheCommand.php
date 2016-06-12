<?php

namespace Anezi\ImagineBundle\Command;

use Anezi\ImagineBundle\Imagine\Cache\CacheManager;
use Anezi\ImagineBundle\Imagine\Data\DataManager;
use Anezi\ImagineBundle\Imagine\Filter\FilterManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ResolveCacheCommand.
 */
class ResolveCacheCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('anezi:imagine:cache:resolve')
            ->setDescription('Resolve cache for given path and set of filters.')
            ->addArgument('paths', InputArgument::REQUIRED | InputArgument::IS_ARRAY, 'Image paths')
            ->addOption(
                'loaders',
                'l',
                InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                'Loaders list'
            )->addOption(
                'filters',
                'f',
                InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                'Filters list'
            )->setHelp(<<<'EOF'
The <info>%command.name%</info> command resolves cache by specified parameters.
It returns list of urls.

<info>php app/console %command.name% path1 path2 --filters=thumb1</info>
Cache for this two paths will be resolved with passed filter.
As a result you will get<info>
    http://localhost/media/cache/thumb1/path1
    http://localhost/media/cache/thumb1/path2</info>

You can pass few filters:
<info>php app/console %command.name% path1 --filters=thumb1 --filters=thumb2</info>
As a result you will get<info>
    http://localhost/media/cache/thumb1/path1
    http://localhost/media/cache/thumb2/path1</info>

If you omit --filters parameter then to resolve given paths will be used all configured and available filters in application:
<info>php app/console %command.name% path1</info>
As a result you will get<info>
    http://localhost/media/cache/thumb1/path1
    http://localhost/media/cache/thumb2/path1</info>
EOF
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $paths = $input->getArgument('paths');
        $loaders = $input->getOption('loaders');
        $filters = $input->getOption('filters');

        /* @var FilterManager filterManager */
        $filterManager = $this->getContainer()->get('anezi_imagine.filter.manager');
        /* @var CacheManager cacheManager */
        $cacheManager = $this->getContainer()->get('anezi_imagine.cache.manager');
        /* @var DataManager dataManager */
        $dataManager = $this->getContainer()->get('anezi_imagine.data.manager');

        if (empty($loaders)) {
            $loaders = array_keys($filterManager->getLoaders());
        }

        if (empty($filters)) {
            $filters = array_keys($filterManager->getFilterConfiguration()->all());
        }

        foreach ($paths as $path) {
            foreach ($loaders as $loader) {
                foreach ($filters as $filter) {
                    if (!$cacheManager->isStored($path, $loader, $filter)) {
                        $binary = $dataManager->find($filter, $path);

                        $cacheManager->store(
                            $filterManager->applyFilter($binary, $filter),
                            $path,
                            $loader,
                            $filter
                        );
                    }

                    $output->writeln($cacheManager->resolve($path, $loader, $filter));
                }
            }
        }
    }
}
