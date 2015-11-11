<?php

namespace OroCRM\Bundle\TaskBundle\Form\Handler;

use Oro\Bundle\FormBundle\Utils\FormUtils;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\ActivityBundle\Manager\ActivityManager;
use Oro\Bundle\EntityBundle\Tools\EntityRoutingHelper;

use OroCRM\Bundle\TaskBundle\Entity\Task;

class TaskHandler
{
    /** @var FormInterface */
    protected $form;

    /** @var Request */
    protected $request;

    /** @var ObjectManager */
    protected $manager;

    /** @var  ActivityManager */
    protected $activityManager;

    /** @var EntityRoutingHelper */
    protected $entityRoutingHelper;

    /**
     * @param FormInterface       $form
     * @param Request             $request
     * @param ObjectManager       $manager
     * @param ActivityManager     $activityManager
     * @param EntityRoutingHelper $entityRoutingHelper
     */
    public function __construct(
        FormInterface $form,
        Request $request,
        ObjectManager $manager,
        ActivityManager $activityManager,
        EntityRoutingHelper $entityRoutingHelper
    ) {
        $this->form                = $form;
        $this->request             = $request;
        $this->manager             = $manager;
        $this->activityManager     = $activityManager;
        $this->entityRoutingHelper = $entityRoutingHelper;
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
        $action            = $this->entityRoutingHelper->getAction($this->request);
        $targetEntityClass = $this->entityRoutingHelper->getEntityClassName($this->request);
        $targetEntityId    = $this->entityRoutingHelper->getEntityId($this->request);

        if ($targetEntityClass
            && !$entity->getId()
            && $this->request->getMethod() === 'GET'
            && $action === 'assign'
            && is_a($targetEntityClass, 'Oro\Bundle\UserBundle\Entity\User', true)
        ) {
            $entity->setOwner(
                $this->entityRoutingHelper->getEntity($targetEntityClass, $targetEntityId)
            );
            FormUtils::replaceField($this->form, 'owner', ['read_only' => true]);
        }

        $this->form->setData($entity);

        if (in_array($this->request->getMethod(), array('POST', 'PUT'))) {
            $this->form->submit($this->request);

            if ($this->form->isValid()) {
                // TODO: should be refactored after finishing BAP-8722
                // Contexts handling should be moved to common for activities form handler
                if ($this->form->has('contexts')) {
                    $contexts = $this->form->get('contexts')->getData();
                    $this->activityManager->setActivityTargets($entity, $contexts);
                } elseif ($targetEntityClass && $action === 'activity') {
                    // if we don't have "contexts" form field
                    // we should save association between activity and target manually
                    $this->activityManager->addActivityTarget(
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
