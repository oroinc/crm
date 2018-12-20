<?php

namespace Oro\Bundle\MagentoBundle\Form\Type;

use Oro\Bundle\TranslationBundle\Form\Type\TranslatableEntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

abstract class AbstractApiAddressType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('label', TextType::class, ['required' => false]);
        $builder->add('street', TextType::class, ['required' => false]);
        $builder->add('street2', TextType::class, ['required' => false]);
        $builder->add('city', TextType::class, ['required' => false]);
        $builder->add('postalCode', TextType::class, ['required' => false]);
        $builder->add('regionText', TextType::class, ['required' => false]);
        $builder->add('namePrefix', TextType::class, ['required' => false]);
        $builder->add('firstName', TextType::class, ['required' => true]);
        $builder->add('middleName', TextType::class, ['required' => false]);
        $builder->add('lastName', TextType::class, ['required' => true]);
        $builder->add('nameSuffix', TextType::class, ['required' => false]);
        $builder->add('phone', TextType::class, ['required' => false]);
        $builder->add('primary', CheckboxType::class, ['required' => false]);

        $builder->add(
            'country',
            TranslatableEntityType::class,
            [
                'class' => 'Oro\Bundle\AddressBundle\Entity\Country',
                'choice_label' => 'name',
                'required' => true
            ]
        );
        $builder->add('countryText', TextType::class, ['required' => false]);

        $builder->add(
            'region',
            TranslatableEntityType::class,
            [
                'class' => 'Oro\Bundle\AddressBundle\Entity\Region',
                'choice_label' => 'name',
                'required' => true
            ]
        );

        $builder->add(
            'types',
            TranslatableEntityType::class,
            [
                'class' => 'OroAddressBundle:AddressType',
                'choice_label' => 'label',
                'required' => false,
                'multiple' => true,
                'expanded' => true
            ]
        );
    }
}
