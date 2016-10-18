<?php

namespace Oro\Bundle\ChannelBundle\Form\Type;

use Symfony\Component\Form\FormView;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;

abstract class AbstractChannelAwareType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['channel_id'] = isset($options['channel_id']) ? $options['channel_id'] : null;

        $view->vars['channel_field_name'] = isset($view->parent[$options['channel_field']])
            ? $view->parent[$options['channel_field']]->vars['full_name']
            : $this->suggestFullName($options['channel_field'], $view);
    }

    /**
     * Suggest field full name based on parent view
     *
     * @param string   $name
     * @param FormView $view
     *
     * @return string
     */
    protected function suggestFullName($name, FormView $view)
    {
        $fullName = $name;

        if ('' !== ($parentFullName = $view->parent->vars['full_name'])) {
            $fullName = sprintf('%s[%s]', $parentFullName, $name);
        }

        return $fullName;
    }
}
