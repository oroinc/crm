<?php

namespace Oro\Bundle\FilterBundle\Tests\Unit\Form\Type\Filter;

use Oro\Bundle\FilterBundle\Tests\Unit\Form\Type\AbstractTypeTest;
use Oro\Bundle\FilterBundle\Form\Type\Filter\NumberFilterType;
use Oro\Bundle\FilterBundle\Form\Type\Filter\FilterType;

class NumberFilterTypeTest extends AbstractTypeTest
{
    /**
     * @var NumberFilterType
     */
    private $type;

    /**
     * @var string
     */
    protected $defaultLocale = 'en_US';

    protected function setUp()
    {
        parent::setUp();
        $translator = $this->createMockTranslator();
        $this->type = new NumberFilterType($translator);
        $this->factory->addType(new FilterType($translator));
    }

    /**
     * {@inheritDoc}
     */
    protected function getTestFormType()
    {
        return $this->type;
    }

    public function testGetName()
    {
        $this->assertEquals(NumberFilterType::NAME, $this->type->getName());
    }

    /**
     * {@inheritDoc}
     */
    public function setDefaultOptionsDataProvider()
    {
        return array(
            array(
                'defaultOptions' => array(
                    'field_type' => 'number',
                    'operator_choices' => array(
                        NumberFilterType::TYPE_EQUAL => 'label_type_equal',
                        NumberFilterType::TYPE_GREATER_EQUAL => 'label_type_greater_equal',
                        NumberFilterType::TYPE_GREATER_THAN => 'label_type_greater_than',
                        NumberFilterType::TYPE_LESS_EQUAL => 'label_type_less_equal',
                        NumberFilterType::TYPE_LESS_THAN => 'label_type_less_than',
                    )
                )
            )
        );
    }

    /**
     * {@inheritDoc}
     */
    public function bindDataProvider()
    {
        return array(
            'not formatted number' => array(
                'bindData' => array('type' => NumberFilterType::TYPE_EQUAL, 'value' => '12345.67890'),
                'formData' => array('type' => NumberFilterType::TYPE_EQUAL, 'value' => 12345.6789),
                'viewData' => array('type' => NumberFilterType::TYPE_EQUAL, 'value' => '12,345.68'),
                'customOptions' => array(
                    'field_options' => array('grouping' => true, 'precision' => 2)
                ),
            ),
            'formatted number' => array(
                'bindData' => array('type' => NumberFilterType::TYPE_EQUAL, 'value' => '12,345.68'),
                'formData' => array('type' => NumberFilterType::TYPE_EQUAL, 'value' => 12345.68),
                'viewData' => array('type' => NumberFilterType::TYPE_EQUAL, 'value' => '12,345.68'),
                'customOptions' => array(
                    'field_options' => array('grouping' => true, 'precision' => 2)
                ),
            ),
            'integer' => array(
                'bindData' => array('type' => NumberFilterType::TYPE_EQUAL, 'value' => '12345.67890'),
                'formData' => array('type' => NumberFilterType::TYPE_EQUAL, 'value' => 12345),
                'viewData' => array('type' => NumberFilterType::TYPE_EQUAL, 'value' => '12345'),
                'customOptions' => array(
                    'field_type' => 'integer'
                ),
            ),
            'money' => array(
                'bindData' => array('type' => NumberFilterType::TYPE_EQUAL, 'value' => '12345.67890'),
                'formData' => array('type' => NumberFilterType::TYPE_EQUAL, 'value' => 12345.6789),
                'viewData' => array('type' => NumberFilterType::TYPE_EQUAL, 'value' => '12345.68'),
                'customOptions' => array(
                    'field_type' => 'money'
                ),
            ),
            'invalid format' => array(
                'bindData' => array('type' => NumberFilterType::TYPE_EQUAL, 'value' => 'abcd.67890'),
                'formData' => array('type' => NumberFilterType::TYPE_EQUAL),
                'viewData' => array('type' => NumberFilterType::TYPE_EQUAL, 'value' => 'abcd.67890'),
                'customOptions' => array(
                    'field_type' => 'money'
                ),
            ),
        );
    }
}
