<?php

namespace Oro\Bundle\GridBundle\Tests\Unit\Filter\ORM;

use Oro\Bundle\GridBundle\Filter\ORM\NumberFilter;
use Oro\Bundle\GridBundle\Form\Type\Filter\NumberType;

class NumberFilterTest extends FilterTestCase
{
    /**#@+
     * Test parameters
     */
    const TEST_NAME      = 'test_name';
    const TEST_LABEL     = 'test_label';
    const TEST_TYPE      = 'test_type';
    const TEST_ALIAS     = 'test_alias';
    const TEST_FIELD     = 'test_field';
    const TEST_VALUE     = '3.14';
    const TEST_UNIQUE_ID = 'test_unique_id';
    const TEST_PARAMETER = 'test_name_test_unique_id';
    /**#@-*/

    /**
     * @var array
     */
    protected $filterTypes = array(
        NumberType::TYPE_EQUAL,
        NumberType::TYPE_GREATER_EQUAL,
        NumberType::TYPE_GREATER_THAN,
        NumberType::TYPE_LESS_EQUAL,
        NumberType::TYPE_LESS_THAN,
    );

    /**
     * @var NumberFilter
     */
    protected $model;

    /**
     * @var array
     */
    protected $expectedRenderSettings = array(
        'oro_grid_type_filter_number', array(
            'field_type'    => self::TEST_TYPE,
            'field_options' => array('required' => false),
            'label'         => self::TEST_LABEL
        )
    );

    /**
     * @var array
     */
    protected $knownOperators = array(
        NumberType::TYPE_EQUAL         => '=',
        NumberType::TYPE_GREATER_EQUAL => '>=',
        NumberType::TYPE_GREATER_THAN  => '>',
        NumberType::TYPE_LESS_EQUAL    => '<=',
        NumberType::TYPE_LESS_THAN     => '<',
    );

    protected function setUp()
    {
        $this->model = new NumberFilter($this->getTranslatorMock());
    }

    protected function tearDown()
    {
        unset($this->model);
    }

    public function testGetRenderSettings()
    {
        $this->model->initialize(
            self::TEST_NAME,
            array(
                'label' => self::TEST_LABEL,
                'type'  => self::TEST_TYPE,
            )
        );

        $this->assertEquals($this->expectedRenderSettings, $this->model->getRenderSettings());
    }

    public function testGetTypeOptions()
    {
        $actualTypes = $this->model->getTypeOptions();
        $this->assertTypeOptions($actualTypes);
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
                '$expected' => $this->knownOperators[NumberType::TYPE_EQUAL],
                '$type'     => false,
                '$default'  => NumberType::TYPE_EQUAL
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
        $this->assertEquals($expected, $this->model->getOperator($type, $default));
    }

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
                '$data'      => array(
                    'value' => self::TEST_VALUE,
                    'type'  => NumberType::TYPE_GREATER_THAN
                ),
                '$expected'  => self::TEST_ALIAS . '.' . self::TEST_FIELD . ' > :' . self::TEST_PARAMETER,
                '$isCorrect' => true
            ),
            'correct_default_operator' => array(
                '$data'      => array(
                    'value' => self::TEST_VALUE
                ),
                '$expected'  => self::TEST_ALIAS . '.' . self::TEST_FIELD . ' = :' . self::TEST_PARAMETER,
                '$isCorrect' => true
            )
        );
    }

    /**
     * @param array $data
     * @param string $expected
     * @param bool $isCorrect
     *
     * @dataProvider filterDataProvider
     */
    public function testFilter($data, $expected = null, $isCorrect = false)
    {
        $queryBuilder = $this->getMock(
            'Oro\Bundle\GridBundle\Datagrid\ORM\ProxyQuery',
            array('getUniqueParameterId', 'andWhere', 'setParameter'),
            array(),
            '',
            false
        );
        if ($isCorrect) {
            $queryBuilder->expects($this->once())
                ->method('getUniqueParameterId')
                ->will($this->returnValue(self::TEST_UNIQUE_ID));
            $queryBuilder->expects($this->once())
                ->method('andWhere')
                ->will($this->returnCallback(array($this, 'andWhereCallback')));
            $queryBuilder->expects($this->once())
                ->method('setParameter')
                ->with(self::TEST_PARAMETER, $data['value']);
        }

        $this->model->initialize(self::TEST_NAME, array('field_mapping' => true));
        $this->model->filter($queryBuilder, self::TEST_ALIAS, self::TEST_FIELD, $data);
        $this->assertEquals($expected, $this->actualCondition);
    }

    public function testGetDefaultOptions()
    {
        $defaultOptions = $this->model->getDefaultOptions();
        $this->assertInternalType('array', $defaultOptions);
        $this->assertEmpty($defaultOptions);
    }
}
