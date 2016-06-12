<?php

namespace Anezi\ImagineBundle\Tests\Events;

use Anezi\ImagineBundle\Events\CacheResolveEvent;

/**
 * @covers Anezi\ImagineBundle\Events\CacheResolveEvent
 */
class CacheResolveEventTest extends \PHPUnit_Framework_TestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
    }

    /**
     * @test 
     */
    public function testShouldAllowSetPathInConstruct()
    {
        $event = new CacheResolveEvent('default_path', 'default_loader', 'default_filter');

        $this->assertAttributeEquals('default_path', 'path', $event);
    }

    /**
     * @test
     */
    public function testShouldAllowSetPathByMethod()
    {
        $event = new CacheResolveEvent('default_path', 'default_loader', 'default_filter');
        $event->setPath('new_path');

        $this->assertAttributeEquals('new_path', 'path', $event);
    }

    /**
     * @test
     */
    public function testShouldAllowGetPathWhichWasSetInConstruct()
    {
        $event = new CacheResolveEvent('default_path', 'default_loader', 'default_filter');

        $this->assertEquals('default_path', $event->getPath());
    }

    /**
     * @test
     */
    public function testShouldAllowGetPathWhichWasSetByMethod()
    {
        $event = new CacheResolveEvent('default_path', 'default_loader', 'default_filter');
        $event->setPath('new_path');

        $this->assertEquals('new_path', $event->getPath());
    }

    /**
     * @test
     */
    public function testShouldAllowSetFilterInConstruct()
    {
        $event = new CacheResolveEvent('default_path', 'default_loader', 'default_filter');

        $this->assertAttributeEquals('default_filter', 'filter', $event);
    }

    /**
     * @test
     */
    public function testShouldAllowSetFilterByMethod()
    {
        $event = new CacheResolveEvent('default_path', 'default_loader', 'default_filter');
        $event->setFilter('new_filter');

        $this->assertAttributeEquals('new_filter', 'filter', $event);
    }

    /**
     * @test
     */
    public function testShouldAllowGetFilterWhichWasSetInConstruct()
    {
        $event = new CacheResolveEvent('default_path', 'default_loader', 'default_filter');

        $this->assertEquals('default_filter', $event->getFilter());
    }

    /**
     * @test
     */
    public function testShouldAllowGetFilterWhichWasSetByMethod()
    {
        $event = new CacheResolveEvent('default_path', 'default_loader', 'default_filter');
        $event->setFilter('new_filter');

        $this->assertEquals('new_filter', $event->getFilter());
    }

    /**
     * @test
     */
    public function testShouldAllowSetUrlInConstruct()
    {
        $event = new CacheResolveEvent('default_path', 'default_filter', 'default_url');

        $this->assertAttributeEquals('default_url', 'url', $event);
    }

    /**
     * @test
     */
    public function testShouldAllowSetUrlByMethod()
    {
        $event = new CacheResolveEvent('default_path', 'default_loader', 'default_filter');
        $event->setUrl('new_url');

        $this->assertAttributeEquals('new_url', 'url', $event);
    }

    /**
     * @test
     */
    public function testShouldAllowGetUrlWhichWasSetInConstruct()
    {
        $event = new CacheResolveEvent('default_path', 'default_filter', 'default_url');

        $this->assertEquals('default_url', $event->getUrl());
    }

    /**
     * @test
     */
    public function testShouldAllowGetUrlWhichWasSetByMethod()
    {
        $event = new CacheResolveEvent('default_path', 'default_loader', 'default_filter');
        $event->setUrl('new_url');

        $this->assertEquals('new_url', $event->getUrl());
    }
}
