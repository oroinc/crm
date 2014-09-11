<?php
namespace OroCRM\Bundle\SalesBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class LeadSelectType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            [
                'autocomplete_alias' => 'leads',
                'create_form_route'  => 'orocrm_sales_lead_create',
                'configs'            => [
                    'placeholder' => 'orocrm.sales.form.choose_lead'
                ],
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'oro_entity_create_or_select_inline_channel_aware';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'orocrm_sales_lead_select';
    }
}
