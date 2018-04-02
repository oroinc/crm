<?php

namespace Oro\Bundle\SalesBundle\Tests\Unit\Form\Type\Stub;

use Oro\Bundle\AddressBundle\Form\Type\EmailCollectionType;
use Oro\Bundle\AddressBundle\Form\Type\EmailType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EmailCollectionTypeStub extends EmailCollectionType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'entry_type'     => EmailType::class,
            'entry_options'  => ['data_class' => 'Oro\Bundle\SalesBundle\Entity\B2bCustomerEmail'],
            'multiple' => true,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return EmailCollectionTypeParent::class;
    }
}
