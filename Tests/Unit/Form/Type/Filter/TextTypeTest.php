<?php

namespace Oro\Bundle\FilterBundle\Tests\Unit\Form\Type\Filter;

use Oro\Bundle\FilterBundle\Tests\Unit\Form\Type\AbstractTypeTest;
use Oro\Bundle\FilterBundle\Form\Type\Filter\TextType;
use Oro\Bundle\FilterBundle\Form\Type\Filter\ChoiceType;

class TextTypeTest extends AbstractTypeTest
{
    /**
     * @var TextType
     */
    private $type;

    protected function setUp()
    {
        parent::setUp();
        $translator = $this->createMockTranslator();
        $this->type = new TextType($translator);
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
        $this->assertEquals(TextType::NAME, $this->type->getName());
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
                    'choices' => array(
                        TextType::TYPE_CONTAINS => 'label_type_contains',
                        TextType::TYPE_NOT_CONTAINS => 'label_type_not_contains',
                        TextType::TYPE_EQUAL => 'label_type_equal',
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
            'simple text' => array(
                'bindData' => array('type' => TextType::TYPE_CONTAINS, 'value' => 'text'),
                'formData' => array('type' => TextType::TYPE_CONTAINS, 'value' => 'text'),
                'viewData' => array('type' => TextType::TYPE_CONTAINS, 'value' => 'text'),
            ),
        );
    }
}
