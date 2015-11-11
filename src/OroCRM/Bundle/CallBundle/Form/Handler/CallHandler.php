<?php

namespace OroCRM\Bundle\CallBundle\Form\Handler;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\Request;

use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\ActivityBundle\Manager\ActivityManager;
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

    /** @var ActivityManager */
    protected $activityManager;

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
     * @param ActivityManager        $activityManager
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
        ActivityManager $activityManager,
        CallActivityManager $callActivityManager,
        EntityRoutingHelper $entityRoutingHelper,
        FormFactory $formFactory
    ) {
        $this->formName            = $formName;
        $this->formType            = $formType;
        $this->request             = $request;
        $this->manager             = $manager;
        $this->phoneProvider       = $phoneProvider;
        $this->activityManager     = $activityManager;
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
                $phone = $this->request->query->get('phone');
                if (!$phone) {
                    $phone = $this->phoneProvider->getPhoneNumber($targetEntity);
                }
                $entity->setPhoneNumber($phone);
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
                // TODO: should be refactored after finishing BAP-8722
                // Contexts handling should be moved to common for activities form handler
                if ($this->form->has('contexts')) {
                    $contexts = $this->form->get('contexts')->getData();
                    $this->activityManager->setActivityTargets($entity, $contexts);
                } elseif ($targetEntityClass) {
                    // if we don't have "contexts" form field
                    // we should save association between activity and target manually
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
