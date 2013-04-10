<?php

namespace Oro\Bundle\GridBundle\Tests\Unit\Filter\ORM\Flexible;

use Oro\Bundle\GridBundle\Filter\ORM\Flexible\AbstractFlexibleFilter;
use Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManagerRegistry;

class AbstractFlexibleFilterTest extends \PHPUnit_Framework_TestCase
{
    /**#@+
     * Test parameters
     */
    const TEST_NAME           = 'test_name';
    const TEST_FLEXIBLE_NAME  = 'test_flexible_entity';
    /**#@-*/

    /**
     * @var AbstractFlexibleFilter|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $model;

    /**
     * @var FlexibleManagerRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $flexibleRegistry;

    protected function setUp()
    {
        $this->flexibleRegistry = $this->getMock(
            'Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManagerRegistry',
            array('getManager')
        );
        $this->model = $this->getMockForAbstractClass(
            'Oro\Bundle\GridBundle\Filter\ORM\Flexible\AbstractFlexibleFilter',
            array($this->flexibleRegistry)
        );
    }

    protected function tearDown()
    {
        unset($this->model);
        unset($this->flexibleRegistry);
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Flexible entity filter must have flexible entity name.
     */
    public function testInitializeNoFlexibleName()
    {
        $this->model->initialize(self::TEST_NAME, array());
    }

    public function testInitialize()
    {
        $flexibleManager = $this->getMock(
            'Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManager',
            array(),
            array(),
            '',
            false
        );
        $this->flexibleRegistry->expects($this->once())
            ->method('getManager')
            ->with(self::TEST_FLEXIBLE_NAME)
            ->will($this->returnValue($flexibleManager));

        $this->model->initialize(self::TEST_NAME, array('flexible_name' => self::TEST_FLEXIBLE_NAME));
        $this->assertAttributeEquals($flexibleManager, 'flexibleManager', $this->model);
    }

    public function testGetDefaultOptions()
    {
        $defaultOptions = $this->model->getDefaultOptions();
        $this->assertInternalType('array', $defaultOptions);
        $this->assertEmpty($defaultOptions);
    }

    public function testGetRenderSettings()
    {
        $renderSettings = $this->model->getRenderSettings();
        $this->assertInternalType('array', $renderSettings);
        $this->assertEmpty($renderSettings);
    }
}
