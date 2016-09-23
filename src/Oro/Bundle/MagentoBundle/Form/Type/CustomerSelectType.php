<?php

namespace Oro\Bundle\MagentoBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CustomerSelectType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'configs' => array(
                    'placeholder' => 'oro.magento.form.choose_customer',
                ),
                'autocomplete_alias' => 'oro_magento.customers'
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->getBlockPrefix();
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'oro_customer_select';
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'oro_jqueryselect2_hidden';
    }
}
