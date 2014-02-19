<?php
namespace OroCRM\Bundle\SalesBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class LeadSelectType extends AbstractType
{
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'configs' => array(
                    'placeholder' => 'orocrm.sales.form.choose_lead'
                ),
                'autocomplete_alias' => 'leads'
            )
        );
    }

    public function getParent()
    {
        return 'oro_jqueryselect2_hidden';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'orocrm_sales_lead_select';
    }
}
