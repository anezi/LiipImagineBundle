<?php

namespace Anezi\ImagineBundle\Binary;

interface BinaryInterface
{
    /**
     * @return string
     */
    public function getContent();

    /**
     * @return string
     */
    public function getMimeType();

    /**
     * @return string
     */
    public function getFormat();
}
