<?php

namespace OroCRM\Bundle\CallBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\OptionsResolver\Options;

class CallPhoneType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $defaultConfigs = [
            'allowClear'   => true,
            'placeholder'  => 'orocrm.call.form.choose_or_enter_phone',
            'component'    => 'call-phone'
        ];

        $resolver->setDefaults(
            [
                'suggestions' => [],
                'random_id'   => true,
                'configs'     => $defaultConfigs,
            ]
        );
        $resolver->setNormalizers(
            [
                'configs'     => function (Options $options, $configs) use (&$defaultConfigs) {
                    return array_merge($defaultConfigs, $configs);
                },
                'suggestions' => function (Options $options, $suggestions) {
                    sort($suggestions, SORT_STRING & SORT_FLAG_CASE);

                    return $suggestions;
                }
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['suggestions'] = $options['suggestions'];
        $view->vars['component_options']['suggestions'] = $options['suggestions'];
        $view->vars['component_options']['value'] = $view->vars['value'];
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'genemu_jqueryselect2_hidden';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'orocrm_call_phone';
    }
}
