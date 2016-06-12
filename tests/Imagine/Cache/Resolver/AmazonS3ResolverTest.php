<?php

namespace Anezi\ImagineBundle\tests\Imagine\Cache\Resolver;

use Anezi\ImagineBundle\Imagine\Cache\Resolver\AmazonS3Resolver;
use Anezi\ImagineBundle\Model\Binary;
use Anezi\ImagineBundle\Tests\AbstractTest;

/**
 * @covers Anezi\ImagineBundle\Imagine\Cache\Resolver\AmazonS3Resolver
 */
class AmazonS3ResolverTest extends AbstractTest
{
    /**
     * @test 
     */
    public function testImplementsResolverInterface()
    {
        $rc = new \ReflectionClass('Anezi\ImagineBundle\Imagine\Cache\Resolver\AmazonS3Resolver');

        $this->assertTrue($rc->implementsInterface('Anezi\ImagineBundle\Imagine\Cache\Resolver\ResolverInterface'));
    }

    /**
     * @test
     */
    public function testNoDoubleSlashesInObjectUrlOnResolve()
    {
        $s3 = $this->createAmazonS3Mock();
        $s3
            ->expects($this->once())
            ->method('get_object_url')
            ->with('images.example.com', 'thumb/some-folder/path.jpg');

        $resolver = new AmazonS3Resolver($s3, 'images.example.com');
        $resolver->resolve('/some-folder/path.jpg', 'loader', 'thumb');
    }

    /**
     * @test
     */
    public function testObjUrlOptionsPassedToAmazonOnResolve()
    {
        $s3 = $this->createAmazonS3Mock();
        $s3
            ->expects($this->once())
            ->method('get_object_url')
            ->with('images.example.com', 'thumb/some-folder/path.jpg', 0, ['torrent' => true]);

        $resolver = new AmazonS3Resolver($s3, 'images.example.com');
        $resolver->setObjectUrlOption('torrent', true);
        $resolver->resolve('/some-folder/path.jpg', 'loader', 'thumb');
    }

    /**
     * @test
     */
    public function testThrowsAndLogIfCanNotCreateObjectOnAmazon()
    {
        $binary = new Binary('aContent', 'image/jpeg', 'jpeg');

        $s3 = $this->createAmazonS3Mock();
        $s3
            ->expects($this->once())
            ->method('create_object')
            ->will($this->returnValue($this->createCFResponseMock(false)));

        $logger = $this->getMock('Psr\Log\LoggerInterface');
        $logger
            ->expects($this->once())
            ->method('error');

        $resolver = new AmazonS3Resolver($s3, 'images.example.com');
        $resolver->setLogger($logger);

        $this->setExpectedException(
            'Anezi\ImagineBundle\Exception\Imagine\Cache\Resolver\NotStorableException',
            'The object could not be created on Amazon S3.'
        );
        $resolver->store($binary, 'foobar.jpg', 'loader', 'thumb');
    }

    /**
     * @test
     */
    public function testCreatedObjectOnAmazon()
    {
        $binary = new Binary('aContent', 'image/jpeg', 'jpeg');

        $s3 = $this->createAmazonS3Mock();
        $s3
            ->expects($this->once())
            ->method('create_object')
            ->will($this->returnValue($this->createCFResponseMock(true)));

        $resolver = new AmazonS3Resolver($s3, 'images.example.com');

        $resolver->store($binary, 'foobar.jpg', 'loader', 'thumb');
    }

    /**
     * @test
     */
    public function testIsStoredChecksObjectExistence()
    {
        $s3 = $this->createAmazonS3Mock();
        $s3
            ->expects($this->once())
            ->method('if_object_exists')
            ->will($this->returnValue(false));

        $resolver = new AmazonS3Resolver($s3, 'images.example.com');

        $this->assertFalse($resolver->isStored('/some-folder/path.jpg', 'loader', 'thumb'));
    }

    /**
     * @test
     */
    public function testReturnResolvedImageUrlOnResolve()
    {
        $s3 = $this->createAmazonS3Mock();
        $s3
            ->expects($this->once())
            ->method('get_object_url')
            ->with('images.example.com', 'thumb/some-folder/path.jpg', 0, [])
            ->will($this->returnValue('http://images.example.com/some-folder/path.jpg'));

        $resolver = new AmazonS3Resolver($s3, 'images.example.com');

        $this->assertSame(
            'http://images.example.com/some-folder/path.jpg',
            $resolver->resolve('/some-folder/path.jpg', 'loader', 'thumb')
        );
    }

    /**
     * @test
     */
    public function testDoNothingIfFiltersAndPathsEmptyOnRemove()
    {
        $s3 = $this->createAmazonS3Mock();
        $s3
            ->expects($this->never())
            ->method('if_object_exists');
        $s3
            ->expects($this->never())
            ->method('delete_object');
        $s3
            ->expects($this->never())
            ->method('delete_all_objects');

        $resolver = new AmazonS3Resolver($s3, 'images.example.com');

        $resolver->remove([], [], []);
    }

    /**
     * @test
     */
    public function testRemoveCacheForPathAndFilterOnRemove()
    {
        $s3 = $this->createAmazonS3Mock();
        $s3
            ->expects($this->once())
            ->method('if_object_exists')
            ->with('images.example.com', 'thumb/some-folder/path.jpg')
            ->will($this->returnValue(true));
        $s3
            ->expects($this->once())
            ->method('delete_object')
            ->with('images.example.com', 'thumb/some-folder/path.jpg')
            ->will($this->returnValue($this->createCFResponseMock(true)));

        $resolver = new AmazonS3Resolver($s3, 'images.example.com');

        $resolver->remove(['some-folder/path.jpg'], ['loader'], ['thumb']);
    }

    /**
     * @test
     */
    public function testRemoveCacheForSomePathsAndFilterOnRemove()
    {
        $s3 = $this->createAmazonS3Mock();
        $s3
            ->expects($this->at(0))
            ->method('if_object_exists')
            ->with('images.example.com', 'filter/pathOne.jpg')
            ->will($this->returnValue(true));
        $s3
            ->expects($this->at(1))
            ->method('delete_object')
            ->with('images.example.com', 'filter/pathOne.jpg')
            ->will($this->returnValue($this->createCFResponseMock(true)));
        $s3
            ->expects($this->at(2))
            ->method('if_object_exists')
            ->with('images.example.com', 'filter/pathTwo.jpg')
            ->will($this->returnValue(true));
        $s3
            ->expects($this->at(3))
            ->method('delete_object')
            ->with('images.example.com', 'filter/pathTwo.jpg')
            ->will($this->returnValue($this->createCFResponseMock(true)));

        $resolver = new AmazonS3Resolver($s3, 'images.example.com');

        $resolver->remove(['pathOne.jpg', 'pathTwo.jpg'], ['loader'], ['filter']);
    }

    /**
     * @test
     */
    public function testRemoveCacheForSomePathsAndSomeFiltersOnRemove()
    {
        $s3 = $this->createAmazonS3Mock();
        $s3
            ->expects($this->at(0))
            ->method('if_object_exists')
            ->with('images.example.com', 'filterOne/pathOne.jpg')
            ->will($this->returnValue(true));
        $s3
            ->expects($this->at(1))
            ->method('delete_object')
            ->with('images.example.com', 'filterOne/pathOne.jpg')
            ->will($this->returnValue($this->createCFResponseMock(true)));
        $s3
            ->expects($this->at(2))
            ->method('if_object_exists')
            ->with('images.example.com', 'filterOne/pathTwo.jpg')
            ->will($this->returnValue(true));
        $s3
            ->expects($this->at(3))
            ->method('delete_object')
            ->with('images.example.com', 'filterOne/pathTwo.jpg')
            ->will($this->returnValue($this->createCFResponseMock(true)));
        $s3
            ->expects($this->at(4))
            ->method('if_object_exists')
            ->with('images.example.com', 'filterTwo/pathOne.jpg')
            ->will($this->returnValue(true));
        $s3
            ->expects($this->at(5))
            ->method('delete_object')
            ->with('images.example.com', 'filterTwo/pathOne.jpg')
            ->will($this->returnValue($this->createCFResponseMock(true)));
        $s3
            ->expects($this->at(6))
            ->method('if_object_exists')
            ->with('images.example.com', 'filterTwo/pathTwo.jpg')
            ->will($this->returnValue(true));
        $s3
            ->expects($this->at(7))
            ->method('delete_object')
            ->with('images.example.com', 'filterTwo/pathTwo.jpg')
            ->will($this->returnValue($this->createCFResponseMock(true)));

        $resolver = new AmazonS3Resolver($s3, 'images.example.com');

        $resolver->remove(
            ['pathOne.jpg', 'pathTwo.jpg'],
            ['loader'],
            ['filterOne', 'filterTwo']
        );
    }

    /**
     * @test
     */
    public function testDoNothingWhenObjectNotExistForPathAndFilterOnRemove()
    {
        $s3 = $this->createAmazonS3Mock();
        $s3
            ->expects($this->once())
            ->method('if_object_exists')
            ->with('images.example.com', 'filter/path.jpg')
            ->will($this->returnValue(false));
        $s3
            ->expects($this->never())
            ->method('delete_object');

        $resolver = new AmazonS3Resolver($s3, 'images.example.com');

        $resolver->remove(['path.jpg'], ['loader'], ['filter']);
    }

    /**
     * @test
     */
    public function testLogIfNotDeletedForPathAndFilterOnRemove()
    {
        $s3 = $this->createAmazonS3Mock();
        $s3
            ->expects($this->once())
            ->method('if_object_exists')
            ->with('images.example.com', 'filter/path.jpg')
            ->will($this->returnValue(true));
        $s3
            ->expects($this->once())
            ->method('delete_object')
            ->will($this->returnValue($this->createCFResponseMock(false)));

        $logger = $this->getMock('Psr\Log\LoggerInterface');
        $logger
            ->expects($this->once())
            ->method('error');

        $resolver = new AmazonS3Resolver($s3, 'images.example.com');
        $resolver->setLogger($logger);

        $resolver->remove(['path.jpg'], ['loader'], ['filter']);
    }

    /**
     * @test
     */
    public function testRemoveCacheForFilterOnRemove()
    {
        $s3 = $this->createAmazonS3Mock();
        $s3
            ->expects($this->once())
            ->method('delete_all_objects')
            ->with('images.example.com', '/filter/i')
            ->will($this->returnValue(true));

        $resolver = new AmazonS3Resolver($s3, 'images.example.com');

        $resolver->remove([], ['loader'], ['filter']);
    }

    /**
     * @test
     */
    public function testRemoveCacheForSomeFiltersOnRemove()
    {
        $s3 = $this->createAmazonS3Mock();
        $s3
            ->expects($this->once())
            ->method('delete_all_objects')
            ->with('images.example.com', '/filterOne|filterTwo/i')
            ->will($this->returnValue(true));

        $resolver = new AmazonS3Resolver($s3, 'images.example.com');

        $resolver->remove([], ['loader'], ['filterOne', 'filterTwo']);
    }

    /**
     * @test
     */
    public function testLogIfBatchNotDeletedForFilterOnRemove()
    {
        $s3 = $this->createAmazonS3Mock();
        $s3
            ->expects($this->once())
            ->method('delete_all_objects')
            ->with('images.example.com', '/filter/i')
            ->will($this->returnValue(false));

        $logger = $this->getMock('Psr\Log\LoggerInterface');
        $logger
            ->expects($this->once())
            ->method('error');

        $resolver = new AmazonS3Resolver($s3, 'images.example.com');
        $resolver->setLogger($logger);

        $resolver->remove([], ['loader'], ['filter']);
    }

    /**
     * @param bool $ok
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|\CFResponse
     */
    protected function createCFResponseMock($ok = true)
    {
        $s3Response = $this->getMock('CFResponse', ['isOK'], [], '', false);
        $s3Response
            ->expects($this->once())
            ->method('isOK')
            ->will($this->returnValue($ok));

        return $s3Response;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\AmazonS3
     */
    protected function createAmazonS3Mock()
    {
        $mockedMethods = [
            'if_object_exists',
            'create_object',
            'get_object_url',
            'delete_object',
            'delete_all_objects',
            'authenticate',
        ];

        return $this->getMock('AmazonS3', $mockedMethods, [], '', false);
    }
}
