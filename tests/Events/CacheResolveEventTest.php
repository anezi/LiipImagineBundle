<?php

namespace Anezi\ImagineBundle\tests\Events;

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

        $this->assertAttributeSame('default_path', 'path', $event);
    }

    /**
     * @test
     */
    public function testShouldAllowSetPathByMethod()
    {
        $event = new CacheResolveEvent('default_path', 'default_loader', 'default_filter');
        $event->setPath('new_path');

        $this->assertAttributeSame('new_path', 'path', $event);
    }

    /**
     * @test
     */
    public function testShouldAllowGetPathWhichWasSetInConstruct()
    {
        $event = new CacheResolveEvent('default_path', 'default_loader', 'default_filter');

        $this->assertSame('default_path', $event->getPath());
    }

    /**
     * @test
     */
    public function testShouldAllowGetPathWhichWasSetByMethod()
    {
        $event = new CacheResolveEvent('default_path', 'default_loader', 'default_filter');
        $event->setPath('new_path');

        $this->assertSame('new_path', $event->getPath());
    }

    /**
     * @test
     */
    public function testShouldAllowSetFilterInConstruct()
    {
        $event = new CacheResolveEvent('default_path', 'default_loader', 'default_filter');

        $this->assertAttributeSame('default_filter', 'filter', $event);
    }

    /**
     * @test
     */
    public function testShouldAllowSetFilterByMethod()
    {
        $event = new CacheResolveEvent('default_path', 'default_loader', 'default_filter');
        $event->setFilter('new_filter');

        $this->assertAttributeSame('new_filter', 'filter', $event);
    }

    /**
     * @test
     */
    public function testShouldAllowGetFilterWhichWasSetInConstruct()
    {
        $event = new CacheResolveEvent('default_path', 'default_loader', 'default_filter');

        $this->assertSame('default_filter', $event->getFilter());
    }

    /**
     * @test
     */
    public function testShouldAllowGetFilterWhichWasSetByMethod()
    {
        $event = new CacheResolveEvent('default_path', 'default_loader', 'default_filter');
        $event->setFilter('new_filter');

        $this->assertSame('new_filter', $event->getFilter());
    }

    /**
     * @test
     */
    public function testShouldAllowSetUrlInConstruct()
    {
        $event = new CacheResolveEvent('default_path', 'default_filter', 'default_url');

        $this->assertAttributeSame('default_url', 'url', $event);
    }

    /**
     * @test
     */
    public function testShouldAllowSetUrlByMethod()
    {
        $event = new CacheResolveEvent('default_path', 'default_loader', 'default_filter');
        $event->setUrl('new_url');

        $this->assertAttributeSame('new_url', 'url', $event);
    }

    /**
     * @test
     */
    public function testShouldAllowGetUrlWhichWasSetInConstruct()
    {
        $event = new CacheResolveEvent('default_path', 'default_filter', 'default_url');

        $this->assertSame('default_url', $event->getUrl());
    }

    /**
     * @test
     */
    public function testShouldAllowGetUrlWhichWasSetByMethod()
    {
        $event = new CacheResolveEvent('default_path', 'default_loader', 'default_filter');
        $event->setUrl('new_url');

        $this->assertSame('new_url', $event->getUrl());
    }
}
