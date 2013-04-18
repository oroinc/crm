<?php

namespace Oro\Bundle\GridBundle\Tests\Unit\Filter\ORM;

use Oro\Bundle\FilterBundle\Form\Type\Filter\NumberFilterType;
use Oro\Bundle\GridBundle\Filter\ORM\NumberFilter;

class NumberFilterTest extends FilterTestCase
{
    protected function createTestFilter()
    {
        return new NumberFilter($this->getTranslatorMock());
    }

    public function getOperatorDataProvider()
    {
        return array(
            array(NumberFilterType::TYPE_GREATER_EQUAL, '>='),
            array(NumberFilterType::TYPE_GREATER_THAN, '>'),
            array(NumberFilterType::TYPE_EQUAL, '='),
            array(NumberFilterType::TYPE_LESS_EQUAL, '<='),
            array(NumberFilterType::TYPE_LESS_THAN, '<'),
            array(false, '=')
        );
    }

    public function filterDataProvider()
    {
        return array(
            'not_array_value' => array(
                'data' => '',
                'expectProxyQueryCalls' => array()
            ),
            'no_data' => array(
                'data' => array(),
                'expectProxyQueryCalls' => array()
            ),
            'no_value' => array(
                'data' => array('value' => ''),
                'expectProxyQueryCalls' => array()
            ),
            'not_numeric' => array(
                'data' => array('value' => 'abc'),
                'expectProxyQueryCalls' => array()
            ),
            'equals' => array(
                'data' => array('value' => 123, 'type' => NumberFilterType::TYPE_EQUAL),
                'expectProxyQueryCalls' => array(
                    array('getUniqueParameterId', array(), 'p1'),
                    array('andWhere',
                        array(
                            $this->getExpressionFactory()->eq(
                                self::TEST_ALIAS . '.' . self::TEST_FIELD,
                                ':' . self::TEST_NAME . '_p1'
                            )
                        ), null),
                    array('setParameter', array(self::TEST_NAME . '_p1', 123), null)
                )
            ),
        );
    }

    public function testGetDefaultOptions()
    {
        $this->assertEquals(array('form_type' => NumberFilterType::NAME), $this->model->getDefaultOptions());
    }
}
