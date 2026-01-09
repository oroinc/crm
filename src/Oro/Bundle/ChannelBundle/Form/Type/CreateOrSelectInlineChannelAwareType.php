<?php

namespace Oro\Bundle\ChannelBundle\Form\Type;

use Oro\Bundle\FormBundle\Form\Type\OroEntitySelectOrCreateInlineType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Defines the form type for creating or selecting channel-aware entities inline within parent forms.
 */
class CreateOrSelectInlineChannelAwareType extends AbstractChannelAwareType
{
    public const NAME = 'oro_entity_create_or_select_inline_channel_aware';

    public function getName()
    {
        return $this->getBlockPrefix();
    }

    #[\Override]
    public function getBlockPrefix(): string
    {
        return self::NAME;
    }

    #[\Override]
    public function getParent(): ?string
    {
        return OroEntitySelectOrCreateInlineType::class;
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefined(['channel_id']);
        $resolver->setDefaults(['channel_field' => 'dataChannel', 'channel_required' => true]);
    }

    #[\Override]
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        parent::buildView($view, $form, $options);

        if ($options['configs']['component'] != 'channel-aware') {
            $options['configs']['component'] .= '-channel-aware';
        }
        $view->vars['component_options']['channel_id'] = $view->vars['channel_id'];
        $view->vars['component_options']['channel_field_name'] = $view->vars['channel_field_name'];

        $view->vars['channel_required'] = $options['channel_required'];
        $view->vars = array_replace_recursive($view->vars, ['configs' => $options['configs']]);
    }
}
