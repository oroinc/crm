<?php

namespace OroCRM\Bundle\ChannelBundle\Form\Guesser;

use Symfony\Component\Form\Guess\TypeGuess;
use Symfony\Component\Form\Guess\ValueGuess;
use Symfony\Component\Form\FormTypeGuesserInterface;
use Symfony\Component\Validator\Constraints as Assert;

class ChannelTypeGuesser implements FormTypeGuesserInterface
{
    /**
     * {@inheritdoc}
     */
    public function guessType($className, $property)
    {
        if ($className == 'Oro\Bundle\EmbeddedFormBundle\Entity\EmbeddedForm' && $property == 'dataChannel') {
            return new TypeGuess(
                'orocrm_channel_select_type',
                [
                    'constraints' => [new Assert\NotBlank()]
                ],
                TypeGuess::HIGH_CONFIDENCE
            );
        }
    }

    /**
     * {@inheritDoc}
     */
    public function guessRequired($class, $property)
    {
        if ($class == 'Oro\Bundle\EmbeddedFormBundle\Entity\EmbeddedForm' && $property == 'dataChannel') {
            return new ValueGuess(true, ValueGuess::HIGH_CONFIDENCE);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function guessMaxLength($class, $property)
    {
        return;
    }

    /**
     * {@inheritDoc}
     */
    public function guessPattern($class, $property)
    {
        return;
    }
}
