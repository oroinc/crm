<?php

namespace Oro\Bundle\MagentoBundle\Form\Type;

use Oro\Bundle\AddressBundle\Form\Type\AddressType;
use Oro\Bundle\SoapBundle\Form\EventListener\PatchSubscriber;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CartAddressApiType extends AbstractType
{
    const NAME = 'cart_address_api_type';

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('phone', TextType::class, ['required' => false]);
        $builder->add('countryText', TextType::class, ['required' => false]);
        $builder->remove('organization');

        $builder->addEventSubscriber(new PatchSubscriber());
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class'           => 'Oro\Bundle\MagentoBundle\Entity\CartAddress',
                'single_form'          => true,
                'csrf_protection'      => false
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return AddressType::class;
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
        return self::NAME;
    }
}
