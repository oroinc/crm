<?php

namespace OroCRM\Bundle\TaskBundle\Form\Handler;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\Request;

use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\EntityBundle\Tools\EntityRoutingHelper;

use OroCRM\Bundle\TaskBundle\Entity\Task;
use OroCRM\Bundle\TaskBundle\Entity\Manager\TaskActivityManager;

class TaskHandler
{
    /** @var FormInterface */
    protected $form;

    /** @var string */
    protected $formName;

    /** @var string */
    protected $formType;

    /** @var Request */
    protected $request;

    /** @var ObjectManager */
    protected $manager;

    /** @var TaskActivityManager */
    protected $taskActivityManager;

    /** @var EntityRoutingHelper */
    protected $entityRoutingHelper;

    /** @var FormFactory */
    protected $formFactory;

    /**
     * @param string              $formName
     * @param string              $formType
     * @param Request             $request
     * @param ObjectManager       $manager
     * @param taskActivityManager $taskActivityManager
     * @param EntityRoutingHelper $entityRoutingHelper
     * @param FormFactory         $formFactory
     */
    public function __construct(
        $formName,
        $formType,
        Request $request,
        ObjectManager $manager,
        TaskActivityManager $taskActivityManager,
        EntityRoutingHelper $entityRoutingHelper,
        FormFactory $formFactory
    ) {
        $this->formName            = $formName;
        $this->formType            = $formType;
        $this->request             = $request;
        $this->manager             = $manager;
        $this->taskActivityManager = $taskActivityManager;
        $this->entityRoutingHelper = $entityRoutingHelper;
        $this->formFactory         = $formFactory;
    }

    /**
     * Process form
     *
     * @param  Task $entity
     *
     * @return bool  True on successful processing, false otherwise
     */
    public function process(Task $entity)
    {
        $targetEntityClass = $this->request->get('entityClass');
        $targetEntityId    = $this->request->get('entityId');

        $options = [];

        $this->form = $this->formFactory->createNamed($this->formName, $this->formType, $entity, $options);

        if (in_array($this->request->getMethod(), array('POST', 'PUT'))) {
            $this->form->submit($this->request);

            if ($this->form->isValid()) {
                if ($targetEntityClass) {
                    $this->taskActivityManager->addAssociation(
                        $entity,
                        $this->entityRoutingHelper->getEntityReference($targetEntityClass, $targetEntityId)
                    );
                }
                $this->onSuccess($entity);

                return true;
            }
        }

        return false;
    }

    /**
     * "Success" form handler
     *
     * @param Task $entity
     */
    protected function onSuccess(Task $entity)
    {
        $this->manager->persist($entity);
        $this->manager->flush();
    }

    /**
     * Get form, that build into handler, via handler service
     *
     * @return FormInterface
     */
    public function getForm()
    {
        return $this->form;
    }
}
