<?php

namespace OroCRM\Bundle\CallBundle\Form\Handler;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\Request;

use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\AddressBundle\Provider\PhoneProviderInterface;
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

    /** @var PhoneProviderInterface */
    protected $phoneProvider;

    /** @var CallActivityManager */
    protected $callActivityManager;

    /** @var EntityRoutingHelper */
    protected $entityRoutingHelper;

    /** @var FormFactory */
    protected $formFactory;

    /**
     * @param string                 $formName
     * @param string                 $formType
     * @param Request                $request
     * @param ObjectManager          $manager
     * @param PhoneProviderInterface $phoneProvider
     * @param CallActivityManager    $callActivityManager
     * @param EntityRoutingHelper    $entityRoutingHelper
     * @param FormFactory            $formFactory
     */
    public function __construct(
        $formName,
        $formType,
        Request $request,
        ObjectManager $manager,
        PhoneProviderInterface $phoneProvider,
        CallActivityManager $callActivityManager,
        EntityRoutingHelper $entityRoutingHelper,
        FormFactory $formFactory
    ) {
        $this->formName            = $formName;
        $this->formType            = $formType;
        $this->request             = $request;
        $this->manager             = $manager;
        $this->phoneProvider       = $phoneProvider;
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
                $entity->setPhoneNumber($this->phoneProvider->getPhoneNumber($targetEntity));
            }
            $options = [
                'phone_suggestions' => array_unique(
                    array_map(
                        function ($item) {
                            return $item[0];
                        },
                        $this->phoneProvider->getPhoneNumbers($targetEntity)
                    )
                )
            ];
        }

        $this->form = $this->formFactory->createNamed($this->formName, $this->formType, $entity, $options);
        $this->form->setData($entity);

        if (in_array($this->request->getMethod(), array('POST', 'PUT'))) {
            $this->form->submit($this->request);

            if ($this->form->isValid()) {
                if ($targetEntityClass) {
                    $targetEntity = $this->entityRoutingHelper->getEntity($targetEntityClass, $targetEntityId);
                    $this->callActivityManager->addAssociation($entity, $targetEntity);
                    $phones = $this->phoneProvider->getPhoneNumbers($targetEntity);
                    foreach ($phones as $phone) {
                        if ($entity->getPhoneNumber() === $phone[0]) {
                            $this->callActivityManager->addAssociation($entity, $phone[1]);
                        }
                    }
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
