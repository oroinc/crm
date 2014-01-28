<?php
namespace OroCRM\Bundle\SalesBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class OpportunitySelectType extends AbstractType
{
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'configs' => array(
                    'placeholder' => 'orocrm.sales.form.choose_opportunity',
                    'result_template_twig' => 'OroCRMSalesBundle:Opportunity:Autocomplete/result.html.twig',
                    'selection_template_twig' => 'OroCRMSalesBundle:Opportunity:Autocomplete/selection.html.twig'
                ),
                'autocomplete_alias' => 'opportunities'
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
        return 'orocrm_sales_opportunity_select';
    }
}
