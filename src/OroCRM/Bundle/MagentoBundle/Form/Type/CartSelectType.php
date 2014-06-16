<?php

namespace OroCRM\Bundle\MagentoBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CartSelectType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'configs' => array(
                    'placeholder' => 'orocrm.magento.form.choose_cart',
                    'result_template_twig' => 'OroCRMMagentoBundle:Cart:Autocomplete/result.html.twig',
                    'selection_template_twig' => 'OroCRMMagentoBundle:Cart:Autocomplete/selection.html.twig'
                ),
                'autocomplete_alias' => 'orocrm_magento.carts',
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'orocrm_cart_select';
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'oro_jqueryselect2_hidden';
    }
}
