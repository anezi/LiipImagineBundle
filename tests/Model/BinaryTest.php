<?php

namespace Anezi\ImagineBundle\tests\Model;

use Anezi\ImagineBundle\Model\Binary;

/**
 * @covers Anezi\ImagineBundle\Model\Binary
 */
class BinaryTest extends \PHPUnit_Framework_TestCase
{
    public function testImplementsBinaryInterface()
    {
        $rc = new \ReflectionClass('Anezi\ImagineBundle\Model\Binary');

        $this->assertTrue($rc->implementsInterface('Anezi\ImagineBundle\Binary\BinaryInterface'));
    }

    public function testAllowGetContentSetInConstructor()
    {
        $image = new Binary('theContent', 'image/png', 'png');

        $this->assertSame('theContent', $image->getContent());
    }

    public function testAllowGetMimeTypeSetInConstructor()
    {
        $image = new Binary('aContent', 'image/png', 'png');

        $this->assertSame('image/png', $image->getMimeType());
    }

    public function testAllowGetFormatSetInConstructor()
    {
        $image = new Binary('aContent', 'image/png', 'png');

        $this->assertSame('png', $image->getFormat());
    }
}
