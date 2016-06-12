<?php

namespace Anezi\ImagineBundle\tests\Imagine\Cache\Resolver;

use Anezi\ImagineBundle\Imagine\Cache\Resolver\AwsS3Resolver;
use Anezi\ImagineBundle\Model\Binary;
use Anezi\ImagineBundle\Tests\AbstractTest;

/**
 * @covers Anezi\ImagineBundle\Imagine\Cache\Resolver\AwsS3Resolver
 */
class AwsS3ResolverTest extends AbstractTest
{
    /**
     * @test
     */
    public function testImplementsResolverInterface()
    {
        $rc = new \ReflectionClass('Anezi\ImagineBundle\Imagine\Cache\Resolver\AwsS3Resolver');

        $this->assertTrue($rc->implementsInterface('Anezi\ImagineBundle\Imagine\Cache\Resolver\ResolverInterface'));
    }

    /**
     * @test
     */
    public function testNoDoubleSlashesInObjectUrlOnResolve()
    {
        $s3 = $this->getS3ClientMock();
        $s3
            ->expects($this->once())
            ->method('getObjectUrl')
            ->with('images.example.com', 'thumb/some-folder/path.jpg');

        $resolver = new AwsS3Resolver($s3, 'images.example.com');
        $resolver->resolve('/some-folder/path.jpg', 'loader', 'thumb');
    }

    /**
     * @test
     */
    public function testObjUrlOptionsPassedToS3ClintOnResolve()
    {
        $s3 = $this->getS3ClientMock();
        $s3
            ->expects($this->once())
            ->method('getObjectUrl')
            ->with('images.example.com', 'thumb/some-folder/path.jpg', 0, ['torrent' => true]);

        $resolver = new AwsS3Resolver($s3, 'images.example.com');
        $resolver->setObjectUrlOption('torrent', true);
        $resolver->resolve('/some-folder/path.jpg', 'loader', 'thumb');
    }

    /**
     * @test
     */
    public function testLogNotCreatedObjects()
    {
        $binary = new Binary('aContent', 'image/jpeg', 'jpeg');

        $s3 = $this->getS3ClientMock();
        $s3
            ->expects($this->once())
            ->method('putObject')
            ->will($this->throwException(new \Exception('Put object on amazon failed')));

        $logger = $this->getMock('Psr\Log\LoggerInterface');
        $logger
            ->expects($this->once())
            ->method('error');

        $resolver = new AwsS3Resolver($s3, 'images.example.com');
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
    public function testCreateObjectOnAmazon()
    {
        $binary = new Binary('aContent', 'image/jpeg', 'jpeg');

        $s3 = $this->getS3ClientMock();
        $s3
            ->expects($this->once())
            ->method('putObject')
            ->will($this->returnValue($this->getS3ResponseMock()));

        $resolver = new AwsS3Resolver($s3, 'images.example.com');

        $this->assertNull($resolver->store($binary, 'thumb/foobar.jpg', 'loader', 'thumb'));
    }

    /**
     * @test
     */
    public function testObjectOptionsPassedToS3ClintOnCreate()
    {
        $binary = new Binary('aContent', 'image/jpeg', 'jpeg');

        $s3 = $this->getS3ClientMock();
        $s3
            ->expects($this->once())
            ->method('putObject')
            ->with([
                'CacheControl' => 'max-age=86400',
                'ACL' => 'public-read',
                'Bucket' => 'images.example.com',
                'Key' => 'filter/images/foobar.jpg',
                'Body' => 'aContent',
                'ContentType' => 'image/jpeg',
            ]);

        $resolver = new AwsS3Resolver($s3, 'images.example.com');
        $resolver->setPutOption('CacheControl', 'max-age=86400');
        $resolver->store($binary, 'images/foobar.jpg', 'loader', 'filter');
    }

    /**
     * @test
     */
    public function testIsStoredChecksObjectExistence()
    {
        $s3 = $this->getS3ClientMock();
        $s3
            ->expects($this->once())
            ->method('doesObjectExist')
            ->will($this->returnValue(false));

        $resolver = new AwsS3Resolver($s3, 'images.example.com');

        $this->assertFalse($resolver->isStored('/some-folder/path.jpg', 'loader', 'thumb'));
    }

    /**
     * @test
     */
    public function testReturnResolvedImageUrlOnResolve()
    {
        $s3 = $this->getS3ClientMock();
        $s3
            ->expects($this->once())
            ->method('getObjectUrl')
            ->with('images.example.com', 'thumb/some-folder/path.jpg', 0, [])
            ->will($this->returnValue('http://images.example.com/some-folder/path.jpg'));

        $resolver = new AwsS3Resolver($s3, 'images.example.com');

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
        $s3 = $this->getS3ClientMock();
        $s3
            ->expects($this->never())
            ->method('doesObjectExist');
        $s3
            ->expects($this->never())
            ->method('deleteObject');
        $s3
            ->expects($this->never())
            ->method('deleteMatchingObjects');

        $resolver = new AwsS3Resolver($s3, 'images.example.com');

        $resolver->remove([], [], []);
    }

    /**
     * @test
     */
    public function testRemoveCacheForPathAndFilterOnRemove()
    {
        $s3 = $this->getS3ClientMock();
        $s3
            ->expects($this->once())
            ->method('doesObjectExist')
            ->with('images.example.com', 'thumb/some-folder/path.jpg')
            ->will($this->returnValue(true));
        $s3
            ->expects($this->once())
            ->method('deleteObject')
            ->with([
                'Bucket' => 'images.example.com',
                'Key' => 'thumb/some-folder/path.jpg',
            ])
            ->will($this->returnValue($this->getS3ResponseMock(true)));

        $resolver = new AwsS3Resolver($s3, 'images.example.com');

        $resolver->remove(['some-folder/path.jpg'], ['loader'], ['thumb']);
    }

    /**
     * @test
     */
    public function testRemoveCacheForSomePathsAndFilterOnRemove()
    {
        $s3 = $this->getS3ClientMock();
        $s3
            ->expects($this->at(0))
            ->method('doesObjectExist')
            ->with('images.example.com', 'thumb/pathOne.jpg')
            ->will($this->returnValue(true));
        $s3
            ->expects($this->at(1))
            ->method('deleteObject')
            ->with([
                'Bucket' => 'images.example.com',
                'Key' => 'thumb/pathOne.jpg',
            ])
            ->will($this->returnValue($this->getS3ResponseMock(true)));
        $s3
            ->expects($this->at(2))
            ->method('doesObjectExist')
            ->with('images.example.com', 'thumb/pathTwo.jpg')
            ->will($this->returnValue(true));
        $s3
            ->expects($this->at(3))
            ->method('deleteObject')
            ->with([
                'Bucket' => 'images.example.com',
                'Key' => 'thumb/pathTwo.jpg',
            ])
            ->will($this->returnValue($this->getS3ResponseMock(true)));

        $resolver = new AwsS3Resolver($s3, 'images.example.com');

        $resolver->remove(
            ['pathOne.jpg', 'pathTwo.jpg'],
            ['loader'],
            ['thumb']
        );
    }

    /**
     * @test
     */
    public function testRemoveCacheForSomePathsAndSomeFiltersOnRemove()
    {
        $s3 = $this->getS3ClientMock();
        $s3
            ->expects($this->at(0))
            ->method('doesObjectExist')
            ->with('images.example.com', 'filterOne/pathOne.jpg')
            ->will($this->returnValue(true));
        $s3
            ->expects($this->at(1))
            ->method('deleteObject')
            ->with([
                'Bucket' => 'images.example.com',
                'Key' => 'filterOne/pathOne.jpg',
            ])
            ->will($this->returnValue($this->getS3ResponseMock(true)));
        $s3
            ->expects($this->at(2))
            ->method('doesObjectExist')
            ->with('images.example.com', 'filterOne/pathTwo.jpg')
            ->will($this->returnValue(true));
        $s3
            ->expects($this->at(3))
            ->method('deleteObject')
            ->with([
                'Bucket' => 'images.example.com',
                'Key' => 'filterOne/pathTwo.jpg',
            ])
            ->will($this->returnValue($this->getS3ResponseMock(true)));
        $s3
            ->expects($this->at(4))
            ->method('doesObjectExist')
            ->with('images.example.com', 'filterTwo/pathOne.jpg')
            ->will($this->returnValue(true));
        $s3
            ->expects($this->at(5))
            ->method('deleteObject')
            ->with([
                'Bucket' => 'images.example.com',
                'Key' => 'filterTwo/pathOne.jpg',
            ])
            ->will($this->returnValue($this->getS3ResponseMock(true)));
        $s3
            ->expects($this->at(6))
            ->method('doesObjectExist')
            ->with('images.example.com', 'filterTwo/pathTwo.jpg')
            ->will($this->returnValue(true));
        $s3
            ->expects($this->at(7))
            ->method('deleteObject')
            ->with([
                'Bucket' => 'images.example.com',
                'Key' => 'filterTwo/pathTwo.jpg',
            ])
            ->will($this->returnValue($this->getS3ResponseMock(true)));

        $resolver = new AwsS3Resolver($s3, 'images.example.com');

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
        $s3 = $this->getS3ClientMock();
        $s3
            ->expects($this->once())
            ->method('doesObjectExist')
            ->with('images.example.com', 'thumb/some-folder/path.jpg')
            ->will($this->returnValue(false));
        $s3
            ->expects($this->never())
            ->method('deleteObject');

        $resolver = new AwsS3Resolver($s3, 'images.example.com');
        $resolver->remove(['some-folder/path.jpg'], ['loader'], ['thumb']);
    }

    /**
     * @test
     */
    public function testCatchAndLogExceptionsForPathAndFilterOnRemove()
    {
        $s3 = $this->getS3ClientMock();
        $s3
            ->expects($this->once())
            ->method('doesObjectExist')
            ->with('images.example.com', 'thumb/some-folder/path.jpg')
            ->will($this->returnValue(true));
        $s3
            ->expects($this->once())
            ->method('deleteObject')
            ->will($this->throwException(new \Exception()));

        $logger = $this->getMock('Psr\Log\LoggerInterface');
        $logger
            ->expects($this->once())
            ->method('error');

        $resolver = new AwsS3Resolver($s3, 'images.example.com');
        $resolver->setLogger($logger);
        $resolver->remove(['some-folder/path.jpg'], ['loader'], ['thumb']);
    }

    /**
     * @test
     */
    public function testRemoveCacheForFilterOnRemove()
    {
        $expectedBucket = 'images.example.com';
        $expectedFilter = 'theFilter';

        $s3 = $this->getS3ClientMock();
        $s3
            ->expects($this->once())
            ->method('deleteMatchingObjects')
            ->with($expectedBucket, null, "/$expectedFilter/i");

        $resolver = new AwsS3Resolver($s3, $expectedBucket);

        $resolver->remove([], ['loader'], [$expectedFilter]);
    }

    /**
     * @test
     */
    public function testRemoveCacheForSomeFiltersOnRemove()
    {
        $expectedBucket = 'images.example.com';
        $expectedFilterOne = 'theFilterOne';
        $expectedFilterTwo = 'theFilterTwo';

        $s3 = $this->getS3ClientMock();
        $s3
            ->expects($this->once())
            ->method('deleteMatchingObjects')
            ->with($expectedBucket, null, "/{$expectedFilterOne}|{$expectedFilterTwo}/i");

        $resolver = new AwsS3Resolver($s3, $expectedBucket);

        $resolver->remove([], ['loader'], [$expectedFilterOne, $expectedFilterTwo]);
    }

    /**
     * @test
     */
    public function testCatchAndLogExceptionForFilterOnRemove()
    {
        $expectedBucket = 'images.example.com';
        $expectedFilter = 'theFilter';

        $s3 = $this->getS3ClientMock();
        $s3
            ->expects($this->once())
            ->method('deleteMatchingObjects')
            ->will($this->throwException(new \Exception()));

        $logger = $this->getMock('Psr\Log\LoggerInterface');
        $logger
            ->expects($this->once())
            ->method('error');

        $resolver = new AwsS3Resolver($s3, $expectedBucket);
        $resolver->setLogger($logger);

        $resolver->remove([], ['loader'], [$expectedFilter]);
    }

    /**
     * @test
     */
    protected function getS3ResponseMock($ok = true)
    {
        $s3Response = $this->getMock('Guzzle\Service\Resource\Model');

        return $s3Response;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Aws\S3\S3Client
     */
    protected function getS3ClientMock()
    {
        $mockedMethods = [
            'deleteObject',
            'deleteMatchingObjects',
            'createObject',
            'putObject',
            'doesObjectExist',
            'getObjectUrl',
        ];

        return $this->getMock('Aws\S3\S3Client', $mockedMethods, [], '', false);
    }
}
