<?php

namespace OroCRM\Bundle\CallBundle\Form\Handler;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\Request;

use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\AddressBundle\Model\PhoneHolderInterface;
use Oro\Bundle\AddressBundle\Tools\PhoneHolderHelper;
use Oro\Bundle\EntityBundle\Tools\EntityRoutingHelper;

use OroCRM\Bundle\CallBundle\Entity\Call;
use OroCRM\Bundle\CallBundle\Entity\Manager\CallActivityManager;

class CallHandler
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

    /** @var CallActivityManager */
    protected $callActivityManager;

    /** @var EntityRoutingHelper */
    protected $entityRoutingHelper;

    /** @var FormFactory */
    protected $formFactory;

    /**
     * @param string              $formName
     * @param string              $formType
     * @param Request             $request
     * @param ObjectManager       $manager
     * @param PhoneHolderHelper   $phoneHolderHelper
     * @param CallActivityManager $callActivityManager
     * @param EntityRoutingHelper $entityRoutingHelper
     * @param FormFactory         $formFactory
     */
    public function __construct(
        $formName,
        $formType,
        Request $request,
        ObjectManager $manager,
        PhoneHolderHelper $phoneHolderHelper,
        CallActivityManager $callActivityManager,
        EntityRoutingHelper $entityRoutingHelper,
        FormFactory $formFactory
    ) {
        $this->formName            = $formName;
        $this->formType            = $formType;
        $this->request             = $request;
        $this->manager             = $manager;
        $this->phoneHolderHelper   = $phoneHolderHelper;
        $this->callActivityManager = $callActivityManager;
        $this->entityRoutingHelper = $entityRoutingHelper;
        $this->formFactory         = $formFactory;
    }

    /**
     * Process form
     *
     * @param  Call $entity
     *
     * @return bool  True on successful processing, false otherwise
     */
    public function process(Call $entity)
    {
        $targetEntityClass = $this->request->get('entityClass');
        $targetEntityId    = $this->request->get('entityId');

        $options = [];
        if ($targetEntityClass && $this->request->getMethod() === 'GET') {
            $targetEntity = $this->entityRoutingHelper->getEntity($targetEntityClass, $targetEntityId);
            if (!$entity->getId()) {
                $entity->setPhoneNumber($this->phoneHolderHelper->getPhoneNumber($targetEntity));
            }
            if ($targetEntity instanceof PhoneHolderInterface) {
                $options = ['phone_suggestions' => $targetEntity->getPhoneNumbers()];
            }
        }

        $this->form = $this->formFactory->createNamed($this->formName, $this->formType, $entity, $options);

        if (in_array($this->request->getMethod(), array('POST', 'PUT'))) {
            $this->form->submit($this->request);

            if ($this->form->isValid()) {
                if ($targetEntityClass) {
                    $this->callActivityManager->addAssociation(
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
     * @param Call $entity
     */
    protected function onSuccess(Call $entity)
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
