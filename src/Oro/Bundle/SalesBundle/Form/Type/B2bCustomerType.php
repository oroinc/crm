<?php

namespace Oro\Bundle\SalesBundle\Form\Type;

use Oro\Bundle\AddressBundle\Form\DataTransformer\AddressSameTransformer;
use Oro\Bundle\AddressBundle\Form\Type\AddressType;
use Oro\Bundle\AddressBundle\Form\Type\EmailCollectionType;
use Oro\Bundle\AddressBundle\Form\Type\EmailType;
use Oro\Bundle\AddressBundle\Form\Type\PhoneCollectionType;
use Oro\Bundle\AddressBundle\Form\Type\PhoneType;
use Oro\Bundle\ChannelBundle\Form\Type\ChannelSelectType;
use Oro\Bundle\ContactBundle\Form\Type\ContactSelectType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * B2b customer type form.
 */
class B2bCustomerType extends AbstractType
{
    /** @var PropertyAccessorInterface */
    private $propertyAccessor;

    public function __construct(PropertyAccessorInterface $propertyAccessor)
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
            TextType::class,
            [
                'required' => true,
                'label'    => 'oro.sales.b2bcustomer.name.label'
            ]
        );
        $builder->add(
            'contact',
            ContactSelectType::class,
            [
                'label'    => 'oro.sales.b2bcustomer.contact.label',
                'required' => false,
            ]
        );
        $builder->add(
            'emails',
            EmailCollectionType::class,
            [
                'label'    => 'oro.sales.b2bcustomer.emails.label',
                'entry_type' => EmailType::class,
                'required' => false,
                'entry_options'  => ['data_class' => 'Oro\Bundle\SalesBundle\Entity\B2bCustomerEmail']
            ]
        );
        $builder->add(
            'phones',
            PhoneCollectionType::class,
            [
                'label'    => 'oro.sales.b2bcustomer.phones.label',
                'entry_type' => PhoneType::class,
                'required' => false,
                'entry_options'  => ['data_class' => 'Oro\Bundle\SalesBundle\Entity\B2bCustomerPhone']
            ]
        );
        $builder->add(
            'dataChannel',
            ChannelSelectType::class,
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
            AddressType::class,
            [
                'required' => false
            ]
        );
        $builder->add(
            'billingAddress',
            AddressType::class,
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
