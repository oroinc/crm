<?php

namespace OroCRM\Bundle\ChannelBundle\Form\Type;

use Oro\Bundle\EntityBundle\Form\Type\EntityChoiceType;

class ChannelEntityChoiceType extends EntityChoiceType
{
    const NAME = 'orocrm_channel_entity_choice_form';

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }
}
