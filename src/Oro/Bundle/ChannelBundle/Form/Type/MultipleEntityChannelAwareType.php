<?php

namespace Oro\Bundle\ChannelBundle\Form\Type;

use Oro\Bundle\FormBundle\Form\Type\MultipleEntityType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MultipleEntityChannelAwareType extends AbstractChannelAwareType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['extra_config' => 'channel_aware', 'channel_field' => 'dataChannel']);
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        parent::buildView($view, $form, $options);
        $view->vars['extra_config'] = 'channel_aware';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->getBlockPrefix();
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'oro_multiple_entity_channel_aware';
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return MultipleEntityType::class;
    }
}
