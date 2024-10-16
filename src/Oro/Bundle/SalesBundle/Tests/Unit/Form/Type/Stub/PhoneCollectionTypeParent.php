<?php

namespace Oro\Bundle\SalesBundle\Tests\Unit\Form\Type\Stub;

use Symfony\Component\Form\AbstractType;

class PhoneCollectionTypeParent extends AbstractType
{
    #[\Override]
    public function getBlockPrefix(): string
    {
        return 'test_phone_entity';
    }
}
