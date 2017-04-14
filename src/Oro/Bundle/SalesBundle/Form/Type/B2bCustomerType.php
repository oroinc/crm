<?php

namespace Oro\Bundle\SalesBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\PropertyAccess\PropertyAccessor;

use Oro\Bundle\AddressBundle\Form\DataTransformer\AddressSameTransformer;
use Oro\Bundle\SalesBundle\Entity\B2bCustomer;

class B2bCustomerType extends AbstractType
{
    /** @var PropertyAccessor */
    private $propertyAccessor;

    /**
     * @param PropertyAccessor $propertyAccessor
     */
    public function __construct(PropertyAccessor $propertyAccessor)
    {
        $this->propertyAccessor = $propertyAccessor;
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
        return 'oro_sales_b2bcustomer';
    }

    /**
     * {@inheritdoc}
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
            [
                'label'    => 'oro.sales.b2bcustomer.emails.label',
                'type'     => 'oro_email',
                'required' => false,
                'options'  => ['data_class' => 'Oro\Bundle\SalesBundle\Entity\B2bCustomerEmail']
            ]
        );
        $builder->add(
            'phones',
            'oro_phone_collection',
            [
                'label'    => 'oro.sales.b2bcustomer.phones.label',
                'type'     => 'oro_phone',
                'required' => false,
                'options'  => ['data_class' => 'Oro\Bundle\SalesBundle\Entity\B2bCustomerPhone']
            ]
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
                'required' => false
            ]
        );
        $builder->add(
            'billingAddress',
            'oro_address',
            [
                'required' => false
            ]
        );

        $builder->addModelTransformer(new AddressSameTransformer(
            $this->propertyAccessor,
            ['billing_address', 'shipping_address']
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['data_class' => 'Oro\Bundle\SalesBundle\Entity\B2bCustomer']);
    }
}
