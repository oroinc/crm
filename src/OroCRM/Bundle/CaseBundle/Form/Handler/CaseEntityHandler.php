<?php

namespace OroCRM\Bundle\CaseBundle\Form\Handler;

use Doctrine\Common\Persistence\ObjectManager;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

use Oro\Bundle\SoapBundle\Form\Handler\ApiFormHandler;

use OroCRM\Bundle\CaseBundle\Event\Events;
use OroCRM\Bundle\CaseBundle\Event\FormHandlerEvent;

class CaseEntityHandler extends ApiFormHandler
{
    /**
     * @var EventDispatcherInterface
     */
    protected $dispatcher;

    /**
     * @param FormInterface $form
     * @param Request $request
     * @param ObjectManager $manager
     * @param EventDispatcherInterface $dispatcher
     */
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
        $this->entityManager->persist($entity);

        $this->dispatcher->dispatch(
            Events::BEFORE_SAVE,
            new FormHandlerEvent($this->form, $entity)
        );

        $this->entityManager->flush();
    }
}
