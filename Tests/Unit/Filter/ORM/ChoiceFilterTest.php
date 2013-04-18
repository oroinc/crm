<?php

namespace Oro\Bundle\GridBundle\Tests\Unit\Filter\ORM;

use Oro\Bundle\FilterBundle\Form\Type\Filter\ChoiceFilterType;
use Oro\Bundle\GridBundle\Filter\ORM\ChoiceFilter;

class ChoiceFilterTest extends FilterTestCase
{
    protected function createTestFilter()
    {
        return new ChoiceFilter($this->getTranslatorMock());
    }

    public function getOperatorDataProvider()
    {
        return array(
            array(ChoiceFilterType::TYPE_CONTAINS, 'IN'),
            array(ChoiceFilterType::TYPE_NOT_CONTAINS, 'NOT IN'),
            array(false, 'IN')
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
            'contains' => array(
                'data' => array('value' => 'test', 'type' => ChoiceFilterType::TYPE_CONTAINS),
                'expectProxyQueryCalls' => array(
                    array(
                        'andWhere',
                        array($this->getExpressionFactory()->in(self::TEST_ALIAS . '.' . self::TEST_FIELD, 'test')),
                        null
                    )
                )
            ),
            'not_contains' => array(
                'data' => array('value' => 'test', 'type' => ChoiceFilterType::TYPE_NOT_CONTAINS),
                'expectProxyQueryCalls' => array(
                    array(
                        'andWhere',
                        array($this->getExpressionFactory()->notIn(self::TEST_ALIAS . '.' . self::TEST_FIELD, 'test')),
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
                'form_type' => ChoiceFilterType::NAME
            ),
            $this->model->getDefaultOptions()
        );
    }
}
