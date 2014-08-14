<?php

namespace OroCRM\Bundle\ChannelBundle\Form\Type;

use Oro\Bundle\FormBundle\Form\DataTransformer\ArrayToJsonTransformer;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class ChannelEntityType extends AbstractType
{
    const NAME = 'orocrm_channel_entities';

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'hidden';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addViewTransformer(new ArrayToJsonTransformer());
    }
}
