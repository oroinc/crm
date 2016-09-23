<?php

namespace Oro\Bundle\SalesBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Oro\Bundle\SalesBundle\Entity\B2bCustomer;

class B2bCustomerType extends AbstractType
{
    /**
     * @return string
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
        return 'oro_sales_b2bcustomer';
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
                'label'    => 'oro.sales.b2bcustomer.name.label'
            ]
        );
        $builder->add(
            'account',
            'oro_account_select',
            [
                'required' => true,
                'label'    => 'oro.sales.b2bcustomer.account.label'
            ]
        );
        $builder->add(
            'contact',
            'oro_contact_select',
            [
                'label'    => 'oro.sales.b2bcustomer.contact.label',
                'required' => false,
            ]
        );
        $builder->add(
            'emails',
            'oro_email_collection',
            array(
                'label'    => 'oro.sales.b2bcustomer.emails.label',
                'type'     => 'oro_email',
                'required' => false,
                'options'  => array('data_class' => 'Oro\Bundle\SalesBundle\Entity\B2bCustomerEmail')
            )
        );
        $builder->add(
            'phones',
            'oro_phone_collection',
            array(
                'label'    => 'oro.sales.b2bcustomer.phones.label',
                'type'     => 'oro_phone',
                'required' => false,
                'options'  => array('data_class' => 'Oro\Bundle\SalesBundle\Entity\B2bCustomerPhone')
            )
        );
        $builder->add(
            'dataChannel',
            'oro_channel_select_type',
            [
                'required' => true,
                'label'    => 'oro.sales.b2bcustomer.data_channel.label',
                'entities' => [
                    'Oro\\Bundle\\SalesBundle\\Entity\\B2bCustomer'
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
        $resolver->setDefaults(['data_class' => 'Oro\Bundle\SalesBundle\Entity\B2bCustomer']);
    }
}
