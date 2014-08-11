<?php

namespace OroCRM\Bundle\ChannelBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;

use Oro\Bundle\EntityBundle\Form\Type\EntityChoiceType;
use Oro\Bundle\FormBundle\Form\DataTransformer\ArrayToJsonTransformer;

class ChannelEntityChoiceType extends EntityChoiceType
{
    const NAME = 'orocrm_channel_entity_choice_form';

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->addViewTransformer(new ArrayToJsonTransformer());
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }
}
