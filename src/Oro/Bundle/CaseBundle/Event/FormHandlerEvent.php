<?php

namespace Oro\Bundle\CaseBundle\Event;

use Symfony\Component\Form\FormInterface;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Dispatched during case form handling to allow listeners to react to form submission events
 * and modify form data or entity state.
 */
class FormHandlerEvent extends Event
{
    /**
     * @var mixed
     */
    protected $entity;

    /**
     * @var FormInterface
     */
    protected $form;

    /**
     * @param FormInterface $form
     * @param mixed         $entity
     */
    public function __construct(FormInterface $form, $entity)
    {
        $this->form = $form;
        $this->entity = $entity;
    }

    /**
     * @return mixed
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * @return FormInterface
     */
    public function getForm()
    {
        return $this->form;
    }
}
