<?php

namespace Oro\Bundle\UIBundle\Tests\Unit\Twig;

use Oro\Bundle\UIBundle\Twig\CeilExtension;

class CeilExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CeilExtension
     */
    private $extension;

    /**
     * Set up test environment
     */
    public function setUp()
    {
        $this->extension = new CeilExtension();
    }

    public function testName()
    {
        $this->assertEquals('oro_ceil', $this->extension->getName());
    }

    public function testCeil()
    {
        $this->assertEquals(5, $this->extension->ceil(4.6));
        $this->assertEquals(5, $this->extension->ceil(4.1));
    }

    public function testSetFilters()
    {
        $this->assertArrayHasKey('ceil', $this->extension->getFilters());
    }
}
