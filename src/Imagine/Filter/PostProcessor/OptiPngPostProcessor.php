<?php

namespace Anezi\ImagineBundle\Imagine\Filter\PostProcessor;

use Anezi\ImagineBundle\Binary\BinaryInterface;
use Anezi\ImagineBundle\Binary\FileBinaryInterface;
use Anezi\ImagineBundle\Model\Binary;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\ProcessBuilder;

class OptiPngPostProcessor implements PostProcessorInterface
{
    /** @var string Path to optipng binary */
    protected $optipng;

    /**
     * Constructor.
     *
     * @param string $optipngBin Path to the optipng binary
     */
    public function __construct($optipngBin = '/usr/bin/optipng')
    {
        $this->optipngBin = $optipngBin;
    }

    /**
     * @param BinaryInterface $binary
     *
     * @throws ProcessFailedException
     *
     * @return BinaryInterface
     *
     * @see      Implementation taken from Assetic\Filter\optipngFilter
     */
    public function process(BinaryInterface $binary)
    {
        $type = strtolower($binary->getMimeType());
        if (!in_array($type, ['image/png'], true)) {
            return $binary;
        }

        if (false === $input = tempnam(sys_get_temp_dir(), 'imagine_optipng')) {
            throw new \RuntimeException(sprintf('Temp file can not be created in "%s".', sys_get_temp_dir()));
        }

        $pb = new ProcessBuilder([$this->optipngBin]);
        $pb->add('--o7');
        $pb->add($input);

        if ($binary instanceof FileBinaryInterface) {
            copy($binary->getPath(), $input);
        } else {
            file_put_contents($input, $binary->getContent());
        }

        $proc = $pb->getProcess();
        $proc->run();

        if (false !== strpos($proc->getOutput(), 'ERROR') || 0 !== $proc->getExitCode()) {
            unlink($input);
            throw new ProcessFailedException($proc);
        }

        $result = new Binary(file_get_contents($input), $binary->getMimeType(), $binary->getFormat());

        unlink($input);

        return $result;
    }
}
