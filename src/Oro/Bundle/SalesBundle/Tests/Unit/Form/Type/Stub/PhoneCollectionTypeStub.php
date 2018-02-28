<?php

namespace Oro\Bundle\SalesBundle\Tests\Unit\Form\Type\Stub;

use Oro\Bundle\AddressBundle\Form\Type\PhoneCollectionType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PhoneCollectionTypeStub extends PhoneCollectionType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'type'     => 'oro_phone',
            'options'  => ['data_class' => 'Oro\Bundle\SalesBundle\Entity\B2bCustomerPhone'],
            'multiple' => true,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'test_phone_entity';
    }
}
