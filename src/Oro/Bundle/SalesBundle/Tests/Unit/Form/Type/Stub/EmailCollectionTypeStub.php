<?php

namespace Oro\Bundle\SalesBundle\Tests\Unit\Form\Type\Stub;

use Symfony\Component\OptionsResolver\OptionsResolver;

use Oro\Bundle\AddressBundle\Form\Type\EmailCollectionType;

class EmailCollectionTypeStub extends EmailCollectionType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'type'     => 'oro_email',
            'options'  => ['data_class' => 'Oro\Bundle\SalesBundle\Entity\B2bCustomerEmail'],
            'multiple' => true,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'test_email_entity';
    }
}
