<?php

namespace Oro\Bundle\FilterBundle\Tests\Unit\Form\Type\Filter;

use Oro\Bundle\FilterBundle\Tests\Unit\Form\Type\AbstractTypeTest;
use Oro\Bundle\FilterBundle\Form\Type\Filter\NumberType;
use Oro\Bundle\FilterBundle\Form\Type\Filter\ChoiceType;

class NumberTypeTest extends AbstractTypeTest
{
    /**
     * @var NumberType
     */
    private $type;

    protected function setUp()
    {
        parent::setUp();
        $translator = $this->createMockTranslator();
        $this->type = new NumberType($translator);
        $this->factory->addType(new ChoiceType($translator));
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
        $this->assertEquals(NumberType::NAME, $this->type->getName());
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
                    'choices' => array(
                        NumberType::TYPE_EQUAL => 'label_type_equal',
                        NumberType::TYPE_GREATER_EQUAL => 'label_type_greater_equal',
                        NumberType::TYPE_GREATER_THAN => 'label_type_greater_than',
                        NumberType::TYPE_LESS_EQUAL => 'label_type_less_equal',
                        NumberType::TYPE_LESS_THAN => 'label_type_less_than',
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
                'bindData' => array('type' => NumberType::TYPE_EQUAL, 'value' => '12345.67890'),
                'formData' => array('type' => NumberType::TYPE_EQUAL, 'value' => 12345.6789),
                'viewData' => array('type' => NumberType::TYPE_EQUAL, 'value' => '12,345.68'),
                'customOptions' => array(
                    'field_options' => array('grouping' => true, 'precision' => 2)
                ),
            ),
            'formatted number' => array(
                'bindData' => array('type' => NumberType::TYPE_EQUAL, 'value' => '12,345.68'),
                'formData' => array('type' => NumberType::TYPE_EQUAL, 'value' => 12345.68),
                'viewData' => array('type' => NumberType::TYPE_EQUAL, 'value' => '12,345.68'),
                'customOptions' => array(
                    'field_options' => array('grouping' => true, 'precision' => 2)
                ),
            ),
            'integer' => array(
                'bindData' => array('type' => NumberType::TYPE_EQUAL, 'value' => '12345.67890'),
                'formData' => array('type' => NumberType::TYPE_EQUAL, 'value' => 12345),
                'viewData' => array('type' => NumberType::TYPE_EQUAL, 'value' => '12345'),
                'customOptions' => array(
                    'field_type' => 'integer'
                ),
            ),
            'money' => array(
                'bindData' => array('type' => NumberType::TYPE_EQUAL, 'value' => '12345.67890'),
                'formData' => array('type' => NumberType::TYPE_EQUAL, 'value' => 12345.6789),
                'viewData' => array('type' => NumberType::TYPE_EQUAL, 'value' => '12345.68'),
                'customOptions' => array(
                    'field_type' => 'money'
                ),
            ),
        );
    }
}
