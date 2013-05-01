<?php

namespace Oro\Bundle\GridBundle\Tests\Unit\Datagrid;

use Oro\Bundle\GridBundle\Datagrid\FlexibleDatagridManager;
use Oro\Bundle\FlexibleEntityBundle\Model\AbstractAttributeType;
use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;
use Oro\Bundle\GridBundle\Filter\FilterInterface;

class FlexibleDatagridManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test parameters
     */
    const TEST_FLEXIBLE_NAME = 'test_flexible_name';

    /**
     * @var FlexibleDatagridManager
     */
    protected $model;

    /**
     * @var array
     */
    protected $testAttributes = array('attribute_1', 'attribute_2');

    /**
     * @var array
     */
    protected $typeToField = array(
        AbstractAttributeType::BACKEND_TYPE_DATE     => FieldDescriptionInterface::TYPE_DATE,
        AbstractAttributeType::BACKEND_TYPE_DATETIME => FieldDescriptionInterface::TYPE_DATETIME,
        AbstractAttributeType::BACKEND_TYPE_DECIMAL  => FieldDescriptionInterface::TYPE_DECIMAL,
        AbstractAttributeType::BACKEND_TYPE_INTEGER  => FieldDescriptionInterface::TYPE_INTEGER,
        AbstractAttributeType::BACKEND_TYPE_OPTION   => FieldDescriptionInterface::TYPE_OPTIONS,
        AbstractAttributeType::BACKEND_TYPE_TEXT     => FieldDescriptionInterface::TYPE_TEXT,
        AbstractAttributeType::BACKEND_TYPE_VARCHAR  => FieldDescriptionInterface::TYPE_TEXT,
        AbstractAttributeType::BACKEND_TYPE_PRICE    => FieldDescriptionInterface::TYPE_TEXT,
        AbstractAttributeType::BACKEND_TYPE_METRIC   => FieldDescriptionInterface::TYPE_TEXT,
    );

    /**
     * @var array
     */
    protected $typeToFilter = array(
        AbstractAttributeType::BACKEND_TYPE_DATE     => FilterInterface::TYPE_FLEXIBLE_DATE,
        AbstractAttributeType::BACKEND_TYPE_DATETIME => FilterInterface::TYPE_FLEXIBLE_DATETIME,
        AbstractAttributeType::BACKEND_TYPE_DECIMAL  => FilterInterface::TYPE_FLEXIBLE_NUMBER,
        AbstractAttributeType::BACKEND_TYPE_INTEGER  => FilterInterface::TYPE_FLEXIBLE_NUMBER,
        AbstractAttributeType::BACKEND_TYPE_OPTION   => FilterInterface::TYPE_FLEXIBLE_OPTIONS,
        AbstractAttributeType::BACKEND_TYPE_TEXT     => FilterInterface::TYPE_FLEXIBLE_STRING,
        AbstractAttributeType::BACKEND_TYPE_VARCHAR  => FilterInterface::TYPE_FLEXIBLE_STRING,
        AbstractAttributeType::BACKEND_TYPE_PRICE    => FilterInterface::TYPE_FLEXIBLE_STRING,
        AbstractAttributeType::BACKEND_TYPE_METRIC   => FilterInterface::TYPE_FLEXIBLE_STRING,
    );

    protected function setUp()
    {
        $this->model = $this->getMockForAbstractClass('Oro\Bundle\GridBundle\Datagrid\FlexibleDatagridManager');
    }

    protected function tearDown()
    {
        unset($this->model);
    }

    public function testSetFlexibleManager()
    {
        $flexibleManagerMock = $this->getMock(
            'Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManager',
            array('setLocale', 'setScope'),
            array(),
            '',
            false
        );
        $flexibleManagerMock->expects($this->once())->method('setLocale')->with($this->isType('string'));
        $flexibleManagerMock->expects($this->once())->method('setScope')->with($this->isType('string'));

        $this->assertAttributeEmpty('flexibleManager', $this->model);
        $this->model->setFlexibleManager($flexibleManagerMock);
        $this->assertAttributeEquals($flexibleManagerMock, 'flexibleManager', $this->model);
    }

    public function testGetFlexibleAttributes()
    {
        $attributeRepositoryMock = $this->getMockForAbstractClass(
            'Doctrine\Common\Persistence\ObjectRepository',
            array(),
            '',
            false,
            true,
            true,
            array('findBy')
        );
        $attributeRepositoryMock->expects($this->once())
            ->method('findBy')
            ->with(array('entityType' => self::TEST_FLEXIBLE_NAME))
            ->will($this->returnValue($this->testAttributes));

        $flexibleManagerMock = $this->getMock(
            'Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManager',
            array('getAttributeRepository', 'getFlexibleName'),
            array(),
            '',
            false
        );
        $flexibleManagerMock->expects($this->once())
            ->method('getAttributeRepository')
            ->will($this->returnValue($attributeRepositoryMock));
        $flexibleManagerMock->expects($this->once())
            ->method('getFlexibleName')
            ->will($this->returnValue(self::TEST_FLEXIBLE_NAME));

        $this->model->setFlexibleManager($flexibleManagerMock);

        $this->assertAttributeEmpty('attributes', $this->model);

        // request to get attributes must be invoked only once
        $this->assertEquals($this->testAttributes, $this->model->getFlexibleAttributes());
        $this->assertEquals($this->testAttributes, $this->model->getFlexibleAttributes());

        $this->assertAttributeEquals($this->testAttributes, 'attributes', $this->model);
    }

    public function testConvertFlexibleTypeToFieldType()
    {
        foreach ($this->typeToField as $attributeType => $fieldType) {
            $this->assertEquals($fieldType, $this->model->convertFlexibleTypeToFieldType($attributeType));
        }
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Unknown flexible backend field type.
     */
    public function testConvertFlexibleTypeToFieldTypeUnknownType()
    {
        $this->model->convertFlexibleTypeToFieldType('unknown_attribute_type');
    }

    public function testConvertFlexibleTypeToFilterType()
    {
        foreach ($this->typeToFilter as $attributeType => $filterType) {
            $this->assertEquals($filterType, $this->model->convertFlexibleTypeToFilterType($attributeType));
        }
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Unknown flexible backend filter type.
     */
    public function testConvertFlexibleTypeToFilterTypeUnknownType()
    {
        $this->model->convertFlexibleTypeToFilterType('unknown_attribute_type');
    }
}
