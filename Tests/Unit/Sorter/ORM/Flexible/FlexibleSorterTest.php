<?php

namespace Oro\Bundle\GridBundle\Tests\Unit\Sorter\ORM\Flexible;

use Oro\Bundle\GridBundle\Sorter\ORM\Flexible\FlexibleSorter;

class FlexibleSorterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var FlexibleSorter
     */
    protected $model;

    protected function setUp()
    {
        $containerMock = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $this->model = new FlexibleSorter($containerMock);
    }

    protected function tearDown()
    {
        unset($this->model);
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Flexible entity sorter must have flexible entity name.
     */
    public function testInitialize()
    {
        $fieldDescriptionMock = $this->getMock('Oro\Bundle\GridBundle\Field\FieldDescription');

        $this->model->initialize($fieldDescriptionMock);
    }
}
