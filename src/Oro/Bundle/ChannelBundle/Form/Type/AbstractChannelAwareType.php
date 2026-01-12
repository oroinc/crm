<?php

namespace Oro\Bundle\ChannelBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

/**
 * Provides common functionality for form types that need channel context.
 *
 * This base class handles the propagation of channel ID and channel field name to form views,
 * making channel information available for client-side logic and validation.
 * Subclasses should extend this when creating forms that need to be aware of the channel context.
 */
abstract class AbstractChannelAwareType extends AbstractType
{
    #[\Override]
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
