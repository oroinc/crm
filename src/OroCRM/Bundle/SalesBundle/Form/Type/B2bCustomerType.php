<?php

namespace OroCRM\Bundle\SalesBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use OroCRM\Bundle\SalesBundle\Entity\B2bCustomer;

class B2bCustomerType extends AbstractType
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'orocrm_sales_b2bcustomer';
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'name',
            'text',
            [
                'required' => true,
                'label'    => 'orocrm.sales.b2bcustomer.name.label'
            ]
        );
        $builder->add(
            'account',
            'orocrm_account_select',
            [
                'required' => true,
                'label'    => 'orocrm.sales.b2bcustomer.account.label'
            ]
        );
        $builder->add(
            'contact',
            'orocrm_contact_select',
            [
                'label'    => 'orocrm.sales.b2bcustomer.contact.label',
                'required' => false,
            ]
        );
        $builder->add(
            'dataChannel',
            'orocrm_channel_select_type',
            [
                'required' => true,
                'label'    => 'orocrm.sales.b2bcustomer.data_channel.label',
                'entities' => [
                    'OroCRM\\Bundle\\SalesBundle\\Entity\\B2bCustomer'
                ],
            ]
        );
        $builder->add(
            'shippingAddress',
            'oro_address',
            [
                'cascade_validation' => true,
                'required'           => false
            ]
        );
        $builder->add(
            'billingAddress',
            'oro_address',
            [
                'cascade_validation' => true,
                'required'           => false
            ]
        );
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(['data_class' => 'OroCRM\Bundle\SalesBundle\Entity\B2bCustomer']);
    }
}
