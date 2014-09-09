<?php

namespace OroCRM\Bundle\MarketingListBundle\Tests\Unit\Form\Type;

use OroCRM\Bundle\MarketingListBundle\Form\Type\MarketingListSelectType;

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
        $resolver = $this->getMock('Symfony\Component\OptionsResolver\OptionsResolverInterface');
        $resolver->expects($this->once())
            ->method('setDefaults')
            ->with(
                [
                    'autocomplete_alias' => 'marketing_lists',
                    'create_form_route'  => 'orocrm_marketing_list_create',
                    'configs'            => [
                        'placeholder' => 'orocrm.marketinglist.form.choose_marketing_list'
                    ],
                ]
            );

        $this->type->setDefaultOptions($resolver);
    }

    public function testGetName()
    {
        $this->assertEquals('orocrm_marketing_list_select', $this->type->getName());
    }

    public function testGetParent()
    {
        $this->assertEquals('oro_entity_create_or_select_inline', $this->type->getParent());
    }
}
