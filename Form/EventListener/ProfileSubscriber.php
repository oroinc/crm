<?php

namespace Oro\Bundle\UserBundle\Form\EventListener;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;

use Oro\Bundle\UserBundle\Acl\Manager as AclManager;
use Oro\Bundle\UserBundle\Entity\User;

class ProfileSubscriber implements EventSubscriberInterface
{
    /**
     * @var FormFactoryInterface
     */
    protected $factory;

    /**
     * @var AclManager
     */
    protected $aclManager;

    /**
     * @var SecurityContextInterface
     */
    protected $security;

    /**
     * @param FormFactoryInterface     $factory    Factory to add new form children
     * @param AclManager               $aclManager ACL manager
     * @param SecurityContextInterface $security   Security context
     */
    public function __construct(FormFactoryInterface $factory, AclManager $aclManager, SecurityContextInterface $security)
    {
        $this->factory    = $factory;
        $this->aclManager = $aclManager;
        $this->security   = $security;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::PRE_SET_DATA => 'preSetData',
            FormEvents::PRE_BIND => 'preBind',
        );
    }

    public function preBind(FormEvent $event)
    {
        $inputData = $event->getData();
        if(isset($inputData['emails'])) {
            foreach($inputData['emails'] as $id => $email) {
                if (!$email['email']) {
                    unset($inputData['emails'][$id]);
                }
            }

            $event->setData($inputData);
        }
    }

    public function preSetData(FormEvent $event)
    {
        /* @var $entity User */
        $entity = $event->getData();
        $form   = $event->getForm();

        if (is_null($entity)) {
            return;
        }

        if ($entity->getId()) {
            $form->remove('plainPassword');
        }

        if ($this->security->getToken() && is_object($user = $this->security->getToken()->getUser())) {
            if (!$this->aclManager->isResourceGranted('oro_user_role', $user)) {
                $form->remove('rolesCollection');
            }

            if (!$this->aclManager->isResourceGranted('oro_user_group', $user)) {
                $form->remove('groups');
            }

            // only admin granted to modify user state
            // but do not allow "admin" to disable his own account
            if ($this->aclManager->isResourceGranted('root', $user)) {
                $form->add($this->factory->createNamed('enabled', 'checkbox', $entity->isEnabled(), array(
                    'required' => false,
                    'disabled' => $entity->getId() == $user->getId(),
                )));
            }
        } else {
            $form->remove('rolesCollection');
            $form->remove('groups');
        }
    }
}
