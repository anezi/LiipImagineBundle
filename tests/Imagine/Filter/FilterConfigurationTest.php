<?php

namespace Anezi\ImagineBundle\Tests\Filter;

use Anezi\ImagineBundle\Imagine\Filter\FilterConfiguration;
use Anezi\ImagineBundle\Tests\AbstractTest;

/**
 * @covers Anezi\ImagineBundle\Imagine\Filter\FilterConfiguration
 */
class FilterConfigurationTest extends AbstractTest
{
    public function testSetAndGetFilter()
    {
        $config = [
            'filters' => [
                'thumbnail' => [
                    'size' => [180, 180],
                    'mode' => 'outbound',
                ],
            ],
            'cache' => 'web_path',
        ];

        $filterConfiguration = new FilterConfiguration();
        $filterConfiguration->set('profile_photo', $config);

        $this->assertSame($config, $filterConfiguration->get('profile_photo'));
    }

    public function testReturnAllFilters()
    {
        $filterConfiguration = new FilterConfiguration();
        $filterConfiguration->set('foo', ['fooConfig']);
        $filterConfiguration->set('bar', ['barConfig']);

        $filters = $filterConfiguration->all();

        $this->assertInternalType('array', $filters);

        $this->assertArrayHasKey('foo', $filters);
        $this->assertSame(['fooConfig'], $filters['foo']);

        $this->assertArrayHasKey('bar', $filters);
        $this->assertSame(['barConfig'], $filters['bar']);
    }

    public function testGetUndefinedFilter()
    {
        $filterConfiguration = new FilterConfiguration();

        $this->setExpectedException('RuntimeException', 'Could not find configuration for a filter: thumbnail');
        $filterConfiguration->get('thumbnail');
    }

    public function testShouldGetSameConfigSetBefore()
    {
        $config = [
            'quality' => 85,
            'format'  => 'jpg',
            'filters' => [
                'thumbnail' => [
                    'size' => [180, 180],
                    'mode' => 'outbound',
                ],
            ],
            'cache' => 'web_path',
        ];

        $filterConfiguration = new FilterConfiguration();
        $filterConfiguration->set('profile_photo', $config);

        $this->assertSame($config, $filterConfiguration->get('profile_photo'));
    }

    public function testGetConfigSetViaConstructor()
    {
        $filterConfiguration = new FilterConfiguration([
            'profile_photo' => [],
            'thumbnail'     => [],
        ]);

        $this->assertInternalType('array', $filterConfiguration->get('profile_photo'));
        $this->assertInternalType('array', $filterConfiguration->get('thumbnail'));
    }
}
