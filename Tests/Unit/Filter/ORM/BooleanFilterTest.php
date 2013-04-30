<?php

namespace Oro\Bundle\GridBundle\Tests\Unit\Filter\ORM;

use Oro\Bundle\FilterBundle\Form\Type\Filter\BooleanFilterType;
use Oro\Bundle\GridBundle\Filter\ORM\BooleanFilter;

class BooleanFilterTest extends FilterTestCase
{
    protected function createTestFilter()
    {
        return new BooleanFilter($this->getTranslatorMock());
    }

    public function filterDataProvider()
    {
        $fieldExpression   = self::TEST_ALIAS . '.' . self::TEST_FIELD;
        $expressionFactory = $this->getExpressionFactory();
        $summaryExpression = $expressionFactory->andX(
            $expressionFactory->isNotNull($fieldExpression),
            $expressionFactory->neq($fieldExpression, $expressionFactory->literal(''))
        );

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
            'incorrect_value' => array(
                'data' => array('value' => 'incorrect_value'),
                'expectProxyQueryCalls' => array()
            ),
            'value_yes' => array(
                'data' => array('value' => BooleanFilterType::TYPE_YES),
                'expectProxyQueryCalls' => array(
                    array(
                        'andWhere',
                        array($summaryExpression),
                        null
                    )
                )
            ),
            'value_no' => array(
                'data' => array('value' => BooleanFilterType::TYPE_NO),
                'expectProxyQueryCalls' => array(
                    array(
                        'andWhere',
                        array($expressionFactory->not($summaryExpression)),
                        null
                    )
                )
            ),
        );
    }

    public function testGetDefaultOptions()
    {
        $this->assertEquals(
            array(
                'form_type' => BooleanFilterType::NAME
            ),
            $this->model->getDefaultOptions()
        );
    }

    public function testGetOperator()
    {
        // do nothing as getOperator method not exist in this class
    }
}
