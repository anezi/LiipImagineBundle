<?php

namespace Anezi\ImagineBundle\Binary;

interface FileBinaryInterface extends BinaryInterface
{
    /**
     * @return string
     */
    public function getPath();
}
