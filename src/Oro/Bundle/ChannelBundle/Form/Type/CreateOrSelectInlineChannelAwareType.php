<?php

namespace Oro\Bundle\ChannelBundle\Form\Type;

use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CreateOrSelectInlineChannelAwareType extends AbstractChannelAwareType
{
    const NAME = 'oro_entity_create_or_select_inline_channel_aware';

    /**
     * {@inheritdoc}
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
        return self::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'oro_entity_create_or_select_inline';
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setOptional(['channel_id']);
        $resolver->setDefaults(['channel_field' => 'dataChannel', 'channel_required' => true]);
    }

    /**
     * {@inheritdoc}
     */
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
