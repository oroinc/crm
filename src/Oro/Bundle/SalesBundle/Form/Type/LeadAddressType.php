<?php

namespace Oro\Bundle\SalesBundle\Form\Type;

use Oro\Bundle\AddressBundle\Form\EventListener\FixAddressesPrimarySubscriber;
use Oro\Bundle\AddressBundle\Form\Type\AddressType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Defines the form type for lead address data input.
 */
class LeadAddressType extends AbstractType
{
    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($options['single_form']) {
            $builder->addEventSubscriber(
                new FixAddressesPrimarySubscriber('owner.addresses')
            );
        }
        $builder->add(
            'primary',
            CheckboxType::class,
            ['required' => false]
        );
    }
    #[\Override]
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => 'Oro\Bundle\SalesBundle\Entity\LeadAddress',
            ]
        );
    }

    #[\Override]
    public function getParent(): ?string
    {
        return AddressType::class;
    }

    public function getName()
    {
        return $this->getBlockPrefix();
    }

    #[\Override]
    public function getBlockPrefix(): string
    {
        return 'oro_sales_lead_address';
    }
}
