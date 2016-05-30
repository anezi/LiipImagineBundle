<?php

namespace Anezi\ImagineBundle\Binary\Loader;

use Symfony\Component\HttpFoundation\File\MimeType\ExtensionGuesserInterface;
use Anezi\ImagineBundle\Model\Binary;
use Anezi\ImagineBundle\Exception\Binary\Loader\NotLoadableException;
use League\Flysystem\FilesystemInterface;

class FlysystemLoader implements LoaderInterface
{
    /**
     * @var FilesystemInterface
     */
    protected $filesystem;

    /**
     * @var ExtensionGuesserInterface
     */
    protected $extensionGuesser;

    public function __construct(
        ExtensionGuesserInterface $extensionGuesser,
        FilesystemInterface $filesystem)
    {
        $this->extensionGuesser = $extensionGuesser;
        $this->filesystem = $filesystem;
    }

    /**
     * {@inheritdoc}
     */
    public function find($path)
    {
        if ($this->filesystem->has($path) === false) {
            throw new NotLoadableException(sprintf('Source image "%s" not found.', $path));
        }

        $mimeType = $this->filesystem->getMimetype($path);

        return new Binary(
            $this->filesystem->read($path),
            $mimeType,
            $this->extensionGuesser->guess($mimeType)
        );
    }
}
