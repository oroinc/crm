<?php

namespace Oro\Bundle\GridBundle\Property;

use Oro\Bundle\GridBundle\Datagrid\ResultRecordInterface;
use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;

class TwigTemplateProperty extends AbstractProperty implements TwigPropertyInterface
{
    /**
     * @var \Twig_Environment
     */
    protected $environment;

    /**
     * @var string
     */
    protected $templateName;

    /**
     * @var FieldDescriptionInterface
     */
    protected $field;

    /**
     * @param FieldDescriptionInterface $field
     * @param string $templateName
     */
    public function __construct(FieldDescriptionInterface $field, $templateName)
    {
        $this->field        = $field;
        $this->templateName = $templateName;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->field->getName();
    }

    /**
     * @param \Twig_Environment $environment
     * @return null
     */
    public function setEnvironment(\Twig_Environment $environment)
    {
        $this->environment = $environment;
    }

    /**
     * Render field template
     *
     * @param ResultRecordInterface $record
     * @return string
     * @throws \LogicException
     */
    public function getValue(ResultRecordInterface $record)
    {
        $template = $this->environment->loadTemplate($this->templateName);
        $context = array(
            'field'  => $this->field,
            'record' => $record,
            'value'  => $record->getValue($this->getName()),
        );

        return $template->render($context);
    }
}
