<?php

namespace Oro\Bundle\SalesBundle\Form\Type;

use Oro\Bundle\SoapBundle\Form\EventListener\PatchSubscriber;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Defines the form type for B2B customer data input in REST API endpoints.
 */
class B2bCustomerApiType extends B2bCustomerType
{
    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder->addEventSubscriber(new PatchSubscriber());
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class'           => 'Oro\Bundle\SalesBundle\Entity\B2bCustomer',
                'csrf_token_id'        => 'b2bcustomer',
                'csrf_protection'      => false
            ]
        );
    }

    #[\Override]
    public function getName()
    {
        return $this->getBlockPrefix();
    }

    #[\Override]
    public function getBlockPrefix(): string
    {
        return 'oro_sales_b2bcustomer_api';
    }
}
