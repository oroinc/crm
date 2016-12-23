<?php

namespace Oro\Bundle\MarketingListBundle\Tests\Unit\Form\Type;

use Oro\Bundle\MarketingListBundle\Form\Type\MarketingListSelectType;

class MarketingListSelectTypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MarketingListSelectType
     */
    protected $type;

    protected function setUp()
    {
        $this->type = new MarketingListSelectType();
    }

    public function testSetDefaultOptions()
    {
        $resolver = $this->createMock('Symfony\Component\OptionsResolver\OptionsResolverInterface');
        $resolver->expects($this->once())
            ->method('setDefaults')
            ->with(
                [
                    'autocomplete_alias' => 'marketing_lists',
                    'create_form_route'  => 'oro_marketing_list_create',
                    'configs'            => [
                        'placeholder' => 'oro.marketinglist.form.choose_marketing_list'
                    ],
                ]
            );

        $this->type->setDefaultOptions($resolver);
    }

    public function testGetName()
    {
        $this->assertEquals('oro_marketing_list_select', $this->type->getName());
    }

    public function testGetParent()
    {
        $this->assertEquals('oro_entity_create_or_select_inline', $this->type->getParent());
    }
}
