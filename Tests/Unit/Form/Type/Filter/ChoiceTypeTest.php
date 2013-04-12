<?php

namespace Oro\Bundle\FilterBundle\Tests\Unit\Form\Type\Filter;

use Oro\Bundle\FilterBundle\Tests\Unit\Form\Type\AbstractTypeTest;
use Oro\Bundle\FilterBundle\Form\Type\Filter\ChoiceType;

class ChoiceTypeTest extends AbstractTypeTest
{
    /**
     * @var ChoiceType
     */
    protected $type;

    protected function setUp()
    {
        parent::setUp();
        $translator = $this->createMockTranslator();
        $this->type = new ChoiceType($translator);
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
        $this->assertEquals(ChoiceType::NAME, $this->type->getName());
    }

    /**
     * {@inheritDoc}
     */
    public function setDefaultOptionsDataProvider()
    {
        return array(
            array(
                'defaultOptions' => array(
                    'field_type' => 'text',
                    'field_options' => array(),
                    'choice_options' => array(),
                ),
                'requiredOptions' => array(
                    'choices', 'choice_options', 'field_type', 'field_options'
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
            'empty data' => array(
                'bindData' => array(),
                'formData' => array('type' => null, 'value' => null),
                'viewData' => array('type' => '', 'value' => ''),
                'customOptions' => array(
                    'choices' => array()
                ),
            ),
            'empty choice' => array(
                'bindData' => array('type' => '1', 'value' => ''),
                'formData' => array('value' => null),
                'viewData' => array('type' => '1', 'value' => ''),
                'customOptions' => array(
                    'choices' => array()
                ),
            ),
            'invalid choice' => array(
                'bindData' => array('type' => '-1', 'value' => ''),
                'formData' => array('value' => null),
                'viewData' => array('type' => '-1', 'value' => ''),
                'customOptions' => array(
                    'choices' => array(
                        1 => 'Choice 1'
                    )
                ),
            ),
            'without choice' => array(
                'bindData' => array('value' => 'text'),
                'formData' => array('type' => null, 'value' => 'text'),
                'viewData' => array('type' => '', 'value' => 'text'),
                'customOptions' => array(
                    'choices' => array(
                        1 => 'Choice 1'
                    )
                ),
            ),
        );
    }
}
