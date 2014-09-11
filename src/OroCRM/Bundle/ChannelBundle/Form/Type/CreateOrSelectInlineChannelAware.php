<?php

namespace OroCRM\Bundle\ChannelBundle\Form\Type;

use Symfony\Component\Form\FormView;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CreateOrSelectInlineChannelAware extends AbstractType
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
        $resolver->setDefaults(['channel_field' => 'dataChannel',]);
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['channel_id'] = isset($options['channel_id']) ? $options['channel_id'] : null;

        $view->vars['channel_field_selector'] = isset($view->parent[$options['channel_field']])
            ? $view->parent[$options['channel_field']]->vars['id']
            : $this->suggestSelector($options['channel_field'], $view);

        $options['configs']['extra_config'] = 'channel_aware';

        $view->vars = array_replace_recursive($view->vars, ['configs' => $options['configs']]);
    }

    /**
     * Suggest field ID based on parent view
     *
     * @param string   $name
     * @param FormView $view
     *
     * @return string
     */
    protected function suggestSelector($name, FormView $view)
    {
        $id = $name;

        if ('' !== ($parentFullName = $view->parent->vars['full_name'])) {
            $id = sprintf('%s_%s', $view->parent->vars['id'], $name);
        }

        return $id;
    }
}
