<?php

namespace Oro\Bundle\CallBundle\DoctrineExtensions\DBAL\Types;

use Doctrine\DBAL\Types\IntegerType;

class DurationType extends IntegerType
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'duration';
    }
}
