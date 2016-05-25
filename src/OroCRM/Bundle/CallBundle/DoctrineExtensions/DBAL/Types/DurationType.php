<?php

namespace OroCRM\Bundle\CallBundle\DoctrineExtensions\DBAL\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
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

    /**
     * {@inheritdoc}
     */
    public function requiresSQLCommentHint(AbstractPlatform $platform)
    {
        return true;
    }
}
