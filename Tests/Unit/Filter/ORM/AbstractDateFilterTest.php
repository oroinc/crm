<?php

namespace Oro\Bundle\GridBundle\Tests\Unit\Filter\ORM;

use Doctrine\ORM\Query\Expr\Comparison;
use Doctrine\ORM\Query\Expr\Orx;
use Oro\Bundle\GridBundle\Form\Type\Filter\DateRangeType;
use Oro\Bundle\GridBundle\Filter\ORM\AbstractDateFilter;

class AbstractDateFilterTest extends \PHPUnit_Framework_TestCase
{
    /**#@+
     * Test parameters
     */
    const TEST_NAME               = 'test_name';
    const TEST_LABEL              = 'test_label';
    const TEST_ALIAS              = 'test_alias';
    const TEST_FIELD              = 'test_field';
    const TEST_FIELD_TYPE         = 'test_field_type';
    const TEST_DATE_START         = '2013-04-08';
    const TEST_DATE_END           = '2013-05-01';
    const TEST_MAPPING_EXPRESSION = 'CONCAT("a", "b")';
    /**#@-*/

    /**
     * @var AbstractDateFilter
     */
    protected $model;

    /**
     * @var int
     */
    protected $uniqueId = 0;

    /**
     * Parameter names
     *
     * @var array
     */
    protected $parameters = array(
        'start' => 'test_name_1',
        'end'   => 'test_name_2',
    );

    /**
     * @var array
     */
    protected $andWhere = array();

    /**
     * @var array
     */
    protected $orWhere = array();

    /**
     * @var array
     */
    protected $andHaving = array();

    /**
     * @var array
     */
    protected $orHaving = array();

    /**
     * @var array
     */
    protected $defaultFieldOptions = array('field_mapping' => true);

    /**
     * @var array
     */
    protected $defaultFormOptions = array('input_type' => 'datetime');

    /**
     * @var array
     */
    protected $expectedRenderSettings = array(
        'field_type'    => self::TEST_FIELD_TYPE,
        'field_options' => array('required' => false),
        'label'         => self::TEST_LABEL,
    );

    protected function setUp()
    {
        $this->markTestSkipped();
        $this->model = $this->getMockForAbstractClass(
            'Oro\Bundle\GridBundle\Filter\ORM\AbstractDateFilter',
            array(),
            '',
            false
        );
    }

    protected function tearDown()
    {
        unset($this->model);
    }

    /**
     * Data provider for testIsParametersCorrect
     *
     * @return array
     */
    public function isParametersCorrectDataProvider()
    {
        return array(
            'no_data'   => array(
                '$data' => array(),
            ),
            'not_array' => array(
                '$data' => 'some_string'
            ),
            'no_value' => array(
                '$data' => array('key' => 'some_string')
            ),
            'no_end_date' => array(
                '$data' => array('value' => array(
                    'start' => self::TEST_DATE_START
                ))
            ),
            'no_start_date' => array(
                '$data' => array('value' => array(
                    'end' => self::TEST_DATE_END
                ))
            ),
            'incorrect_dates' => array(
                '$data' => array('value' => array(
                    'start' => 'incorrect_date',
                    'end'   => 'incorrect_date'
                ))
            ),
            'correct_dates' => array(
                '$data' => array('value' => array(
                    'start' => self::TEST_DATE_START,
                    'end'   => self::TEST_DATE_END,
                )),
                '$expected' => true
            ),
        );
    }

    /**
     * @param mixed $data
     * @param bool $expected
     *
     * @dataProvider isParametersCorrectDataProvider
     */
    public function testIsParametersCorrect($data, $expected = false)
    {
        $result = $this->model->isParametersCorrect($data);
        if ($expected) {
            $this->assertTrue($result);
        } else {
            $this->assertFalse($result);
        }
    }

    public function getFilterParametersDataProvider()
    {
        return array(
            'empty_data' => array(
                '$data' => array(
                    'value' => array(
                        'start' => '',
                        'end'   => ''
                    )
                ),
                '$expected' => array(
                    'date_start'  => '',
                    'date_end'    => '',
                    'filter_type' => DateRangeType::TYPE_BETWEEN
                ),
            ),
            'incorrect_data' => array(
                '$data' => array(
                    'value' => array(
                        'start' => 'incorrect_date',
                        'end'   => 'incorrect_date'
                    ),
                    'type' => 'incorrect_type'
                ),
                '$expected' => array(
                    'date_start'  => '',
                    'date_end'    => '',
                    'filter_type' => DateRangeType::TYPE_BETWEEN
                ),
            ),
            'correct_data' => array(
                '$data' => array(
                    'value' => array(
                        'start' => self::TEST_DATE_START,
                        'end'   => self::TEST_DATE_END
                    ),
                    'type' => DateRangeType::TYPE_NOT_BETWEEN
                ),
                '$expected' => array(
                    'date_start'  => self::TEST_DATE_START,
                    'date_end'    => self::TEST_DATE_END,
                    'filter_type' => DateRangeType::TYPE_NOT_BETWEEN
                ),
            ),
        );
    }

    /**
     * @param array $data
     * @param array $expected
     *
     * @dataProvider getFilterParametersDataProvider
     */
    public function testGetFilterParameters(array $data, array $expected)
    {
        $actual = $this->model->getFilterParameters($data);
        $this->assertEquals($expected, $actual);
    }

    public function filterDataProvider()
    {
        $betweenParameters = array(
            'data' => array(
                'value' => array('start' => self::TEST_DATE_START, 'end' => self::TEST_DATE_END),
                'type'  => DateRangeType::TYPE_BETWEEN
            ),
            'parametersInvocations' => array('start' => 4, 'end' => 5),
        );
        $notBetweenParameters = array(
            'data' => array(
                'value' => array('start' => self::TEST_DATE_START, 'end' => self::TEST_DATE_END),
                'type'  => DateRangeType::TYPE_NOT_BETWEEN
            )  ,
            'parametersInvocations' => array('start' => 3, 'end' => 4),
        );
        $mappingOptions = array(
            'field_mapping' => array('fieldExpression' => self::TEST_MAPPING_EXPRESSION)
        );

        return array(
            'incorrect_parameters' => array(
                '$alias'                 => self::TEST_ALIAS,
                '$field'                 => self::TEST_FIELD,
                '$data'                  => array(),
                '$options'               => array(),
                '$parametersInvocations' => array(),
                '$expected'              => array()
            ),
            'between_where' => array(
                '$alias'                 => self::TEST_ALIAS,
                '$field'                 => self::TEST_FIELD,
                '$data'                  => $betweenParameters['data'],
                '$options'               => array(),
                '$parametersInvocations' => $betweenParameters['parametersInvocations'],
                '$expected' => array(
                    'andWhere' => array(
                        self::TEST_ALIAS . '.' . self::TEST_FIELD . ' >= :' . $this->parameters['start'],
                        self::TEST_ALIAS . '.' . self::TEST_FIELD . ' <= :' . $this->parameters['end'],
                    ),
                )
            ),
            'between_having' => array(
                '$alias'                 => self::TEST_ALIAS,
                '$field'                 => self::TEST_FIELD,
                '$data'                  => $betweenParameters['data'],
                '$options'               => $mappingOptions,
                '$parametersInvocations' => $betweenParameters['parametersInvocations'],
                '$expected' => array(
                    'andHaving' => array(
                        self::TEST_MAPPING_EXPRESSION . ' >= :' . $this->parameters['start'],
                        self::TEST_MAPPING_EXPRESSION . ' <= :' . $this->parameters['end'],
                    ),
                )
            ),
            'not_between_where' => array(
                '$alias'                 => self::TEST_ALIAS,
                '$field'                 => self::TEST_FIELD,
                '$data'                  => $notBetweenParameters['data'],
                '$options'               => array(),
                '$parametersInvocations' => $notBetweenParameters['parametersInvocations'],
                '$expected' => array(
                    'orWhere'  => array(
                        self::TEST_ALIAS . '.' . self::TEST_FIELD . ' < :' . $this->parameters['start'],
                        self::TEST_ALIAS . '.' . self::TEST_FIELD . ' > :' . $this->parameters['end'],
                    )
                )
            ),
            'not_between_having' => array(
                '$alias'                 => self::TEST_ALIAS,
                '$field'                 => self::TEST_FIELD,
                '$data'                  => $notBetweenParameters['data'],
                '$options'               => $mappingOptions,
                '$parametersInvocations' => $notBetweenParameters['parametersInvocations'],
                '$expected' => array(
                    'orHaving'  => array(
                        self::TEST_MAPPING_EXPRESSION . ' < :' . $this->parameters['start'],
                        self::TEST_MAPPING_EXPRESSION . ' > :' . $this->parameters['end'],
                    )
                )
            )
        );
    }

    /**
     * @param string $alias
     * @param string $field
     * @param array $data
     * @param array $options
     * @param array $parametersInvocations
     * @param array $expected
     *
     * @dataProvider filterDataProvider
     */
    public function testFilter(
        $alias,
        $field,
        array $data,
        array $options,
        array $parametersInvocations,
        array $expected
    ) {
        $filterOptions = array_merge($this->defaultFieldOptions, $options);
        $this->model->initialize(self::TEST_NAME, $filterOptions);

        $queryBuilder = $this->getMock(
            'Oro\Bundle\GridBundle\Datagrid\ORM\ProxyQuery',
            array('getUniqueParameterId', 'andWhere', 'andHaving', 'setParameter'),
            array(),
            '',
            false
        );
        $queryBuilder->expects($this->any())
            ->method('getUniqueParameterId')
            ->will($this->returnCallback(array($this, 'getUniqueParameterIdCallback')));
        $queryBuilder->expects($this->any())
            ->method('andWhere')
            ->will($this->returnCallback(array($this, 'addWhereCallback')));
        $queryBuilder->expects($this->any())
            ->method('andHaving')
            ->will($this->returnCallback(array($this, 'addHavingCallback')));
        if (isset($parametersInvocations['start'])) {
            $queryBuilder->expects($this->at($parametersInvocations['start']))
                ->method('setParameter')
                ->with($this->parameters['start'], $data['value']['start']);
        }
        if (isset($parametersInvocations['end'])) {
            $queryBuilder->expects($this->at($parametersInvocations['end']))
                ->method('setParameter')
                ->with($this->parameters['end'], $data['value']['end']);
        }

        $expectedParts = array_merge(
            array(
                'andWhere'  => $this->andWhere,
                'orWhere'   => $this->orWhere,
                'andHaving' => $this->andHaving,
                'orHaving'  => $this->orHaving,
            ),
            $expected
        );

        $this->model->filter($queryBuilder, $alias, $field, $data);

        $this->assertEquals($expectedParts['andWhere'], $this->andWhere);
        $this->assertEquals($expectedParts['orWhere'], $this->orWhere);
        $this->assertEquals($expectedParts['andHaving'], $this->andHaving);
        $this->assertEquals($expectedParts['orHaving'], $this->orHaving);
    }

    /**
     * Callback for QueryBuilder::getUniqueParameterId
     *
     * @return int
     */
    public function getUniqueParameterIdCallback()
    {
        $this->uniqueId++;
        return $this->uniqueId;
    }

    /**
     * Callback for QueryBuilder::andWhere
     *
     * @param Comparison|Orx $comparison
     */
    public function addWhereCallback($comparison)
    {
        if ($comparison instanceof Comparison) {
            $this->andWhere[]
                = $comparison->getLeftExpr() . ' ' . $comparison->getOperator() . ' ' . $comparison->getRightExpr();
        } elseif ($comparison instanceof Orx) {
            /** @var $part Comparison */
            foreach ($comparison->getParts() as $part) {
                $this->orWhere[]
                    = $part->getLeftExpr() . ' ' . $part->getOperator() . ' ' . $part->getRightExpr();
            }
        }
    }

    /**
     * Callback for QueryBuilder::addHaving
     *
     * @param Comparison|Orx $comparison
     */
    public function addHavingCallback($comparison)
    {
        if ($comparison instanceof Comparison) {
            $this->andHaving[]
                = $comparison->getLeftExpr() . ' ' . $comparison->getOperator() . ' ' . $comparison->getRightExpr();
        } elseif ($comparison instanceof Orx) {
            /** @var $part Comparison */
            foreach ($comparison->getParts() as $part) {
                $this->orHaving[]
                    = $part->getLeftExpr() . ' ' . $part->getOperator() . ' ' . $part->getRightExpr();
            }
        }
    }

    public function testGetDefaultOptions()
    {
        $this->assertEquals($this->defaultFormOptions, $this->model->getDefaultOptions());
    }
}
