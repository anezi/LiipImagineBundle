<?php

namespace Anezi\ImagineBundle\Binary\Loader;

use Anezi\ImagineBundle\Exception\Binary\Loader\NotLoadableException;
use Doctrine\ODM\MongoDB\DocumentManager;

class GridFSLoader implements LoaderInterface
{
    /**
     * @var DocumentManager
     */
    protected $dm;

    /**
     * @var string
     */
    protected $class;

    /**
     * @param DocumentManager $dm
     * @param string          $class
     */
    public function __construct(DocumentManager $dm, $class)
    {
        $this->dm = $dm;
        $this->class = $class;
    }

    /**
     * {@inheritdoc}
     */
    public function find($id)
    {
        $image = $this->dm
            ->getRepository($this->class)
            ->find(new \MongoId($id));

        if (!$image) {
            throw new NotLoadableException(sprintf('Source image was not found with id "%s"', $id));
        }

        return $image->getFile()->getBytes();
    }
}
