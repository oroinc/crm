<?php

namespace OroCRM\Bundle\MagentoBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class CustomerAddressType extends AbstractType
{
    const NAME = 'orocrm_magento_customer_addresses';

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
       $builder->add('phone', 'text', ['required' => false, 'label' => 'orocrm.magento.address.phone.label']);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'oro_typed_address';
    }
}
