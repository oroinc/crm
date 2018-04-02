<?php

namespace Oro\Bundle\SalesBundle\Tests\Unit\Form\Type\Stub;

use Symfony\Component\Form\AbstractType;

class EmailCollectionTypeParent extends AbstractType
{
    public function getBlockPrefix()
    {
        return 'test_email_entity';
    }
}
