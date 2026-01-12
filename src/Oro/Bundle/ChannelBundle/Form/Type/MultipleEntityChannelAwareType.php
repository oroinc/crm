<?php

namespace Oro\Bundle\ChannelBundle\Form\Type;

use Oro\Bundle\FormBundle\Form\Type\MultipleEntityType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Defines the form type for selecting multiple channel-aware entities.
 */
class MultipleEntityChannelAwareType extends AbstractChannelAwareType
{
    #[\Override]
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['extra_config' => 'channel_aware', 'channel_field' => 'dataChannel']);
    }

    #[\Override]
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        parent::buildView($view, $form, $options);
        $view->vars['extra_config'] = 'channel_aware';
    }

    public function getName()
    {
        return $this->getBlockPrefix();
    }

    #[\Override]
    public function getBlockPrefix(): string
    {
        return 'oro_multiple_entity_channel_aware';
    }

    #[\Override]
    public function getParent(): ?string
    {
        return MultipleEntityType::class;
    }
}
