<?php

namespace Oro\Bundle\SalesBundle\Form\Type;

use Oro\Bundle\ChannelBundle\Form\Type\CreateOrSelectInlineChannelAwareType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class B2bCustomerSelectType extends AbstractType
{
    #[\Override]
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'configs'            => array(
                    'placeholder' => 'oro.sales.form.choose_b2bcustomer'
                ),
                'autocomplete_alias' => 'b2b_customers',
                'grid_name'          => 'oro-sales-b2bcustomers-select-grid',
                'create_form_route'  => 'oro_sales_b2bcustomer_create',
                'create_enabled'     => true
            )
        );
    }

    #[\Override]
    public function getParent(): ?string
    {
        return CreateOrSelectInlineChannelAwareType::class;
    }

    public function getName()
    {
        return $this->getBlockPrefix();
    }

    #[\Override]
    public function getBlockPrefix(): string
    {
        return 'oro_sales_b2bcustomer_select';
    }
}
