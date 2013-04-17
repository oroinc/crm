<?php

namespace Oro\Bundle\GridBundle\Tests\Unit\Filter\ORM\Flexible;

use Oro\Bundle\GridBundle\Filter\ORM\Flexible\FlexibleStringFilter;
use Oro\Bundle\GridBundle\Datagrid\ORM\ProxyQuery;
use Oro\Bundle\GridBundle\Form\Type\Filter\ChoiceType;

class FlexibleStringFilterTest extends FlexibleFilterTestCase
{
    /**#@+
     * Test parameters
     */
    const TEST_NAME           = 'test_name';
    const TEST_ALIAS          = 'test_alias';
    const TEST_FIELD          = 'test_field';
    const TEST_VALUE          = 'test_value';
    const TEST_TYPE           = 'test_type';
    const TEST_DEFAULT        = 'test_default';
    const TEST_OPERATOR       = 'test_operator';
    const TEST_FLEXIBLE_NAME  = 'test_flexible_entity';
    /**#@-*/

    /**
     * @var FlexibleStringFilter
     */
    protected $model;

    /**
     * @var string
     */
    protected $filterClass = 'Oro\Bundle\GridBundle\Filter\ORM\Flexible\FlexibleStringFilter';

    /**
     * @var string
     */
    protected $parentFilterClass = 'Oro\Bundle\GridBundle\Filter\ORM\StringFilter';

    protected function setUp()
    {
        $this->markTestSkipped();
    }

    public function testGetOperator()
    {
        $parentFilter = $this->prepareParentFilterForGetOperator(
            self::TEST_TYPE,
            self::TEST_DEFAULT,
            self::TEST_OPERATOR
        );

        $this->initializeFilter(array('parentFilter' => $parentFilter));
        $this->assertEquals(self::TEST_OPERATOR, $this->model->getOperator(self::TEST_TYPE, self::TEST_DEFAULT));
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
                    'value' => ''
                ),
            ),
            'correct_defined_operator' => array(
                '$data' => array(
                    'value' => self::TEST_VALUE,
                    'type'  => ChoiceType::TYPE_EQUAL
                ),
                '$expectedOperator'  => '=',
                '$isCorrect' => true
            ),
            'correct_default_operator' => array(
                '$data' => array(
                    'value' => self::TEST_VALUE
                ),
                '$expectedOperator'  => 'LIKE',
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
            $expectedValue = isset($data['type']) ? $data['value'] : '%' . $data['value'] . '%';
            $entityRepository->expects($this->once())
                ->method('applyFilterByAttribute')
                ->with($queryBuilder, self::TEST_FIELD, $expectedValue, $expectedOperator);
        }

        $flexibleRegistry = $this->prepareFlexibleRegistryForFilter($entityRepository, self::TEST_FLEXIBLE_NAME);

        $this->initializeFilter(array('flexibleRegistry' => $flexibleRegistry));
        $this->model->initialize(self::TEST_NAME, array('flexible_name' => self::TEST_FLEXIBLE_NAME));
        $this->model->filter($proxyQuery, self::TEST_ALIAS, self::TEST_FIELD, $data);
    }
}
