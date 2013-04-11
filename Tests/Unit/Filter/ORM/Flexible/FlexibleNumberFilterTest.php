<?php

namespace Oro\Bundle\GridBundle\Tests\Unit\Filter\ORM\Flexible;

use Oro\Bundle\GridBundle\Filter\ORM\Flexible\FlexibleNumberFilter;
use Oro\Bundle\GridBundle\Datagrid\ORM\ProxyQuery;
use Oro\Bundle\GridBundle\Form\Type\Filter\NumberType;

class FlexibleNumberFilterTest extends FlexibleFilterTestCase
{
    /**#@+
     * Test parameters
     */
    const TEST_NAME           = 'test_name';
    const TEST_ALIAS          = 'test_alias';
    const TEST_FIELD          = 'test_field';
    const TEST_VALUE          = '2.7';
    const TEST_TYPE           = 'test_type';
    const TEST_DEFAULT        = 'test_default';
    const TEST_OPERATOR       = 'test_operator';
    const TEST_FLEXIBLE_NAME  = 'test_flexible_entity';
    /**#@-*/

    /**
     * @var FlexibleNumberFilter
     */
    protected $model;

    /**
     * @var string
     */
    protected $filterClass = 'Oro\Bundle\GridBundle\Filter\ORM\Flexible\FlexibleNumberFilter';

    /**
     * @var string
     */
    protected $parentFilterClass = 'Oro\Bundle\GridBundle\Filter\ORM\NumberFilter';

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
            'incorrect_not_numeric' => array(
                '$data' => array(
                    'value' => 'stringData'
                ),
            ),
            'correct_defined_operator' => array(
                '$data' => array(
                    'value' => self::TEST_VALUE,
                    'type'  => NumberType::TYPE_GREATER_THAN
                ),
                '$expectedOperator'  => '>',
                '$isCorrect' => true
            ),
            'correct_default_operator' => array(
                '$data' => array(
                    'value' => self::TEST_VALUE
                ),
                '$expectedOperator'  => '=',
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
            $entityRepository->expects($this->once())
                ->method('applyFilterByAttribute')
                ->with($queryBuilder, self::TEST_FIELD, $data['value'], $expectedOperator);
        }

        $flexibleRegistry = $this->prepareFlexibleRegistryForFilter($entityRepository, self::TEST_FLEXIBLE_NAME);

        $this->initializeFilter(array('flexibleRegistry' => $flexibleRegistry));
        $this->model->initialize(self::TEST_NAME, array('flexible_name' => self::TEST_FLEXIBLE_NAME));
        $this->model->filter($proxyQuery, self::TEST_ALIAS, self::TEST_FIELD, $data);
    }
}
