<?php

namespace OroCRM\Bundle\SalesBundle\Form\Type;

use Oro\Bundle\AddressBundle\Form\Type\AddressType;
use Symfony\Component\Form\FormBuilderInterface;

class LeadAddressType extends AddressType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder
            ->remove('firstName')
            ->remove('lastName');
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'orocrm_lead_address';
    }
}
