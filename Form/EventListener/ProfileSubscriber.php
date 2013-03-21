<?php

namespace Oro\Bundle\UserBundle\Form\EventListener;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;

use Oro\Bundle\UserBundle\Acl\Manager as AclManager;
use Oro\Bundle\UserBundle\Entity\User;

class ProfileSubscriber implements EventSubscriberInterface
{
    /**
     * @var AclManager
     */
    protected $aclManager;

    /**
     * @var SecurityContextInterface
     */
    protected $security;

    /**
     * @param AclManager $aclManager ACL manager
     */
    public function __construct(AclManager $aclManager, SecurityContextInterface $security)
    {
        $this->aclManager = $aclManager;
        $this->security   = $security;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(FormEvents::PRE_SET_DATA => 'preSetData');
    }

    public function preSetData(FormEvent $event)
    {
        /* @var $user User */
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
        } else {
            $form->remove('rolesCollection');
            $form->remove('groups');
        }
    }
}
