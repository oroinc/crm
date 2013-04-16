<?php

namespace Oro\Bundle\FilterBundle\Twig;

use Symfony\Component\Form\FormView;
use Symfony\Component\Form\Extension\Core\View\ChoiceView;

class RenderJsExtension extends \Twig_Extension
{
    /**
     * Extension name
     */
    const NAME = 'oro_filter_render_js';

    /**
     * JS block suffix
     */
    const SUFFIX = '_js';

    /**
     * @var \Twig_Template
     */
    protected $templateName;

    /**
     * @param string $templateName
     */
    public function __construct($templateName)
    {
        $this->templateName = $templateName;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * @param \Twig_Environment $environment
     * @return \Twig_Template
     */
    protected function getTemplate(\Twig_Environment $environment)
    {
        return $environment->loadTemplate($this->templateName);
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction(
                'oro_filter_render_js',
                array($this, 'renderJs'),
                array(
                    'is_safe' => array('html'),
                    'needs_environment' => true
                )
            )
        );
    }

    /**
     * Render JS code for specified filter form view
     *
     * @param \Twig_Environment $environment
     * @param FormView $formView
     * @return string
     */
    public function renderJs(\Twig_Environment $environment, FormView $formView)
    {
        if (!$formView->vars['block_prefixes'] || !is_array($formView->vars['block_prefixes'])) {
            return '';
        }

        $template = $this->getTemplate($environment);

        // start from the last element
        $prefixes = array_reverse($formView->vars['block_prefixes']);

        foreach ($prefixes as $prefix) {
            $blockName = $prefix . self::SUFFIX;
            if ($template->hasBlock($blockName)) {
                return $template->renderBlock(
                    $blockName,
                    array('formView' => $formView)
                );
            }
        }

        return '';
    }

    /**
     * @return array
     */
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter(
                'oro_filter_choices',
                array($this, 'getChoices')
            )
        );
    }

    /**
     * Convert array of choice views to plain array
     *
     * @param array $choices
     * @return array
     */
    public function getChoices(array $choices)
    {
        $result = array();
        foreach ($choices as $choice) {
            if ($choice instanceof ChoiceView) {
                $result[$choice->data] = $choice->label;
            }
        }
        return $result;
    }
}
