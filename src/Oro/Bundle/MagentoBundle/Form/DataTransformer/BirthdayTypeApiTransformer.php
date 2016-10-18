<?php

namespace Oro\Bundle\MagentoBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;

class BirthdayTypeApiTransformer implements DataTransformerInterface
{
    /**
     * {@inheritdoc}
     */
    public function transform($value)
    {
        if ($value instanceof \DateTime) {
            return $value->format('Y-m-d');
        }

        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($value)
    {
        return $value;
    }
}
