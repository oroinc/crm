<?php

namespace OroCRM\Bundle\CaseBundle\Form\Handler;

use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\SoapBundle\Form\Handler\ApiFormHandler;
use OroCRM\Bundle\CaseBundle\Event\Events;
use OroCRM\Bundle\CaseBundle\Event\FormHandlerEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class CaseApiFormHandler extends ApiFormHandler
{
    /**
     * @var EventDispatcherInterface
     */
    protected $dispatcher;

    public function __construct(
        FormInterface $form,
        Request $request,
        ObjectManager $manager,
        EventDispatcherInterface $dispatcher
    ) {
        parent::__construct($form, $request, $manager);
        $this->dispatcher = $dispatcher;
    }

    /**
     * {@inheritdoc}
     */
    protected function onSuccess($entity)
    {
        $this->manager->persist($entity);

        $this->dispatcher->dispatch(
            Events::CASE_FORM_HANDLER_SUCCESS,
            new FormHandlerEvent($this->form, $entity)
        );

        $this->manager->flush();
    }
}
