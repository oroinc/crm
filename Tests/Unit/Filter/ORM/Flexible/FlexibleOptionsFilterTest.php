<?php

namespace Oro\Bundle\GridBundle\Tests\Unit\Filter\ORM\Flexible;

use Oro\Bundle\GridBundle\Filter\ORM\Flexible\FlexibleOptionsFilter;
use Oro\Bundle\GridBundle\Datagrid\ORM\ProxyQuery;
use Oro\Bundle\GridBundle\Form\Type\Filter\ChoiceType;
use Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManagerRegistry;
use Oro\Bundle\FlexibleEntityBundle\Entity\AttributeOption;
use Oro\Bundle\FlexibleEntityBundle\Entity\Mapping\AbstractEntityAttributeOptionValue;

class FlexibleOptionsFilterTest extends FlexibleFilterTestCase
{
    /**#@+
     * Test parameters
     */
    const TEST_NAME          = 'test_name';
    const TEST_LABEL         = 'test_label';
    const TEST_ALIAS         = 'test_alias';
    const TEST_FIELD         = 'test_field';
    const TEST_FLEXIBLE_NAME = 'test_flexible_entity';
    const TEST_ATTRIBUTE     = 'test_attribute';
    /**#@-*/

    /**
     * @var FlexibleOptionsFilter
     */
    protected $model;

    /**
     * @var array
     */
    protected $knownOperators = array(
        ChoiceType::TYPE_CONTAINS     => 'IN',
        ChoiceType::TYPE_NOT_CONTAINS => 'NOT IN',
        ChoiceType::TYPE_EQUAL        => '=',
    );

    /**
     * @var int
     */
    protected $testScalarValue = 2;

    /**
     * @var array
     */
    protected $testArrayValue = array(1, 2, 3);

    /**
     * @var array
     */
    protected $testAttributeOptions = array(
        'key1' => 'option1',
        'key2' => 'option2',
        'key3' => 'option3',
    );

    protected function setUp()
    {
        $this->markTestSkipped();
    }

    protected function initializeFilter(FlexibleManagerRegistry $flexibleRegistry = null)
    {
        if (!$flexibleRegistry) {
            $flexibleRegistry = $this->getMock('Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManagerRegistry');
        }

        $this->model = new FlexibleOptionsFilter($flexibleRegistry);
    }

    /**
     * Data provider testGetOperator
     *
     * @return array
     */
    public function getOperatorDataProvider()
    {
        $cases = array(
            'no_operator' => array(
                '$expected' => false,
                '$type'     => false,
            ),
            'default_operator' => array(
                '$expected' => $this->knownOperators[ChoiceType::TYPE_CONTAINS],
                '$type'     => false,
                '$default'  => ChoiceType::TYPE_CONTAINS
            ),

        );
        foreach ($this->knownOperators as $operator => $string) {
            $key = 'operator_' . $string;
            $cases[$key] = array(
                '$expected' => $string,
                '$type'     => $operator,
            );
        }

        return $cases;
    }

    /**
     * @param string|bool $expected
     * @param int $type
     * @param int $default
     *
     * @dataProvider getOperatorDataProvider
     */
    public function testGetOperator($expected, $type, $default = null)
    {
        $this->initializeFilter();
        $this->assertEquals($expected, $this->model->getOperator($type, $default));
    }

    public function testGetDefaultOptions()
    {
        $this->initializeFilter();
        $defaultOptions = $this->model->getDefaultOptions();
        $this->assertInternalType('array', $defaultOptions);
        $this->assertEmpty($defaultOptions);
    }

    /**
     * Data provider for testFilter
     *
     * @return array
     */
    public function filterDataProvider()
    {
        return array(
            'incorrect_no_data' => array(
                '$data' => array(),
            ),
            'incorrect_type' => array(
                '$data' => 'incorrectData',
            ),
            'incorrect_no_value' => array(
                '$data' => array(
                    'key' => 'value'
                ),
            ),
            'incorrect_empty_value' => array(
                '$data' => array(
                    'value' => array('')
                ),
            ),
            'correct_defined_operator' => array(
                '$data' => array(
                    'value' => $this->testArrayValue,
                    'type'  => ChoiceType::TYPE_NOT_CONTAINS
                ),
                '$expectedOperator'  => 'NOT IN',
                '$isCorrect' => true
            ),
            'correct_default_operator' => array(
                '$data' => array(
                    'value' => $this->testScalarValue
                ),
                '$expectedOperator'  => 'IN',
                '$isCorrect' => true
            )
        );
    }

    /**
     * @param array $data
     * @param string|null $expectedOperator
     * @param bool $isCorrect
     *
     * @dataProvider filterDataProvider
     */
    public function testFilter($data, $expectedOperator = null, $isCorrect = false)
    {
        $queryBuilder = $this->getMock('Doctrine\ORM\QueryBuilder', array(), array(), '', false);
        $proxyQuery = new ProxyQuery($queryBuilder);

        $entityRepository = $this->getMock(
            'Oro\Bundle\FlexibleEntityBundle\Entity\Repository\FlexibleEntityRepository',
            array('applyFilterByAttribute'),
            array(),
            '',
            false
        );
        if ($isCorrect) {
            $expectedValue = is_array($data['value']) ? $data['value'] : array($data['value']);
            $entityRepository->expects($this->once())
                ->method('applyFilterByAttribute')
                ->with($queryBuilder, self::TEST_FIELD, $expectedValue, $expectedOperator);
        }

        $flexibleRegistry = $this->prepareFlexibleRegistryForFilter($entityRepository, self::TEST_FLEXIBLE_NAME);

        $this->initializeFilter($flexibleRegistry);
        $this->model->initialize(self::TEST_NAME, array('flexible_name' => self::TEST_FLEXIBLE_NAME));
        $this->model->filter($proxyQuery, self::TEST_ALIAS, self::TEST_FIELD, $data);
    }

    public function testGetValueOptions()
    {
        $flexibleRegistry = $this->prepareFlexibleRegistryWithOptions($this->testAttributeOptions);

        $this->initializeFilter($flexibleRegistry);
        $this->model->initialize(
            self::TEST_NAME,
            array('flexible_name' => self::TEST_FLEXIBLE_NAME, 'field_name' => self::TEST_FIELD)
        );

        $this->assertEquals($this->testAttributeOptions, $this->model->getValueOptions());
    }

    /**
     * @param array $options
     * @return FlexibleManagerRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function prepareFlexibleRegistryWithOptions(array $options)
    {
        $attributeRepository = $this->getMockForAbstractClass(
            'Doctrine\Common\Persistence\ObjectRepository',
            array(),
            '',
            false,
            true,
            true,
            array('findOneBy')
        );
        $attributeRepository->expects($this->once())
            ->method('findOneBy')
            ->with(array('entityType' => self::TEST_FLEXIBLE_NAME, 'code' => self::TEST_FIELD))
            ->will($this->returnValue(self::TEST_ATTRIBUTE));

        $optionsRepository = $this->getMockForAbstractClass(
            'Doctrine\Common\Persistence\ObjectRepository',
            array(),
            '',
            false,
            true,
            true,
            array('findBy')
        );
        $optionsRepository->expects($this->once())
            ->method('findBy')
            ->with(array('attribute' => self::TEST_ATTRIBUTE))
            ->will($this->returnValue($this->prepareOptions($options)));

        $flexibleManager = $this->getMock(
            'Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManager',
            array('getAttributeRepository', 'getFlexibleName', 'getAttributeOptionRepository'),
            array(),
            '',
            false
        );
        $flexibleManager->expects($this->once())
            ->method('getAttributeRepository')
            ->will($this->returnValue($attributeRepository));
        $flexibleManager->expects($this->once())
            ->method('getFlexibleName')
            ->will($this->returnValue(self::TEST_FLEXIBLE_NAME));
        $flexibleManager->expects($this->once())
            ->method('getAttributeOptionRepository')
            ->will($this->returnValue($optionsRepository));

        return $this->prepareFlexibleRegistry($flexibleManager, self::TEST_FLEXIBLE_NAME);
    }

    /**
     * @param array $data
     * @return array
     */
    protected function prepareOptions(array $data)
    {
        $result = array();
        foreach ($data as $key => $value) {
            /** @var $optionValue AbstractEntityAttributeOptionValue|\PHPUnit_Framework_MockObject_MockObject */
            $optionValue = $this->getMockForAbstractClass(
                'Oro\Bundle\FlexibleEntityBundle\Entity\Mapping\AbstractEntityAttributeOptionValue'
            );
            $optionValue->setValue($value);

            /** @var $option AttributeOption|\PHPUnit_Framework_MockObject_MockObject */
            $option = $this->getMock(
                'Oro\Bundle\FlexibleEntityBundle\Entity\AttributeOption',
                array('getOptionValue')
            );
            $option->setId($key);
            $option->expects($this->any())
                ->method('getOptionValue')
                ->will($this->returnValue($optionValue));

            $result[] = $option;
        }

        return $result;
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage There is no flexible attribute with name test_field.
     */
    public function testGetValueOptionsNoAttribute()
    {
        $attributeRepository = $this->getMockForAbstractClass(
            'Doctrine\Common\Persistence\ObjectRepository',
            array(),
            '',
            false,
            true,
            true,
            array('findOneBy')
        );
        $attributeRepository->expects($this->once())
            ->method('findOneBy')
            ->will($this->returnValue(null));

        $flexibleManager = $this->getMock(
            'Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManager',
            array('getAttributeRepository', 'getFlexibleName', 'getAttributeOptionRepository'),
            array(),
            '',
            false
        );
        $flexibleManager->expects($this->once())
            ->method('getAttributeRepository')
            ->will($this->returnValue($attributeRepository));

        $flexibleRegistry = $this->prepareFlexibleRegistry($flexibleManager, self::TEST_FLEXIBLE_NAME);

        $this->initializeFilter($flexibleRegistry);
        $this->model->initialize(
            self::TEST_NAME,
            array('flexible_name' => self::TEST_FLEXIBLE_NAME, 'field_name' => self::TEST_FIELD)
        );

        $this->model->getValueOptions();
    }
}
