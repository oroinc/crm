<?php

namespace OroCRM\Bundle\ChannelBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\InvalidArgumentException;
use Symfony\Component\Form\Exception\UnexpectedTypeException;

class ChannelStatusApiTransformer implements DataTransformerInterface
{
    /**
     * {@inheritdoc}
     */
    public function transform($value)
    {
        if (null === $value) {
            return null;
        }

        return $value ? 'Active' : 'Inactive';
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($value)
    {
        if (!$value) {
            return null;
        }

        if (!is_string($value)) {
            throw new UnexpectedTypeException($value, 'string');
        }

        switch ($value) {
            case 'Active':
                return true;
            case 'Inactive':
                return false;
            default:
                throw new InvalidArgumentException(
                    sprintf('Expected values "Active" or "Inactive", "%s" given', $value)
                );
        }
    }
}
