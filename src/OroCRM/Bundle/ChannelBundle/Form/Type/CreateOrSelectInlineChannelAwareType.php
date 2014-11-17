<?php

namespace OroCRM\Bundle\ChannelBundle\Form\Type;

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

        $extraConfig = $options['configs']['extra_config'];
        if ($extraConfig != 'channel_aware') {
            $options['configs']['extra_config'] .= '_channel_aware';
        }

        $view->vars['channel_required'] = $options['channel_required'];
        $view->vars = array_replace_recursive($view->vars, ['configs' => $options['configs']]);
    }
}
