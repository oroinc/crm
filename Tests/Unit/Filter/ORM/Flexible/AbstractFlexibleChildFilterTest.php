<?php

namespace Oro\Bundle\GridBundle\Tests\Unit\Filter\ORM\Flexible;

use Oro\Bundle\GridBundle\Filter\ORM\Flexible\AbstractFlexibleChildFilter;
use Oro\Bundle\GridBundle\Filter\FilterInterface;

class AbstractFlexibleChildFilterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AbstractFlexibleChildFilter|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $model;

    /**
     * @var FilterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $parentFilter;

    /**
     * @var array
     */
    protected $testOptions = array('test' => 'options');

    protected function setUp()
    {
        $flexibleRegistry = $this->getMock('Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManagerRegistry');
        $this->parentFilter = $this->getMockForAbstractClass(
            'Oro\Bundle\GridBundle\Filter\FilterInterface',
            array(),
            '',
            false,
            true,
            true,
            array('getDefaultOptions', 'getRenderSettings', 'getTypeOptions')
        );
        $this->model = $this->getMockForAbstractClass(
            'Oro\Bundle\GridBundle\Filter\ORM\Flexible\AbstractFlexibleChildFilter',
            array($flexibleRegistry, $this->parentFilter)
        );
    }

    protected function tearDown()
    {
        unset($this->model);
        unset($this->parentFilter);
    }

    public function testGetDefaultOptions()
    {
        $this->parentFilter->expects($this->once())
            ->method('getDefaultOptions')
            ->will($this->returnValue($this->testOptions));

        $this->assertEquals($this->testOptions, $this->model->getDefaultOptions());
    }

    public function testGetRenderSettings()
    {
        $this->parentFilter->expects($this->once())
            ->method('getRenderSettings')
            ->will($this->returnValue($this->testOptions));

        $this->assertEquals($this->testOptions, $this->model->getRenderSettings());
    }

    public function testGetTypeOptions()
    {
        $this->parentFilter->expects($this->once())
            ->method('getTypeOptions')
            ->will($this->returnValue($this->testOptions));

        $this->assertEquals($this->testOptions, $this->model->getTypeOptions());
    }
}
