<?php

namespace OroCRM\Bundle\MagentoBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class OrderSelectType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'configs' => array(
                    'placeholder' => 'orocrm.magento.form.choose_order',
                    'result_template_twig' => 'OroCRMMagentoBundle:Order:Autocomplete/result.html.twig',
                    'selection_template_twig' => 'OroCRMMagentoBundle:Order:Autocomplete/selection.html.twig'
                ),
                'autocomplete_alias' => 'orocrm_magento.orders'
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'orocrm_order_select';
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'oro_jqueryselect2_hidden';
    }
}
