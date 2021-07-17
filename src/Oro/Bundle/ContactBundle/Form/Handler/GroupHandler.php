<?php

namespace Oro\Bundle\ContactBundle\Form\Handler;

use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\ContactBundle\Entity\Group;
use Oro\Bundle\FormBundle\Form\Handler\RequestHandlerTrait;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class GroupHandler
{
    use RequestHandlerTrait;

    /**
     * @var FormInterface
     */
    protected $form;

    /**
     * @var RequestStack
     */
    protected $requestStack;

    /**
     * @var ObjectManager
     */
    protected $manager;

    public function __construct(FormInterface $form, RequestStack $requestStack, ObjectManager $manager)
    {
        $this->form = $form;
        $this->requestStack = $requestStack;
        $this->manager = $manager;
    }

    /**
     * Process form
     *
     * @param  Group $entity
     * @return bool  True on successfull processing, false otherwise
     */
    public function process(Group $entity)
    {
        $this->form->setData($entity);

        $request = $this->requestStack->getCurrentRequest();
        if (in_array($request->getMethod(), ['POST', 'PUT'], true)) {
            $this->submitPostPutRequest($this->form, $request);

            if ($this->form->isValid()) {
                $appendContacts = $this->form->get('appendContacts')->getData();
                $removeContacts = $this->form->get('removeContacts')->getData();
                $this->onSuccess($entity, $appendContacts, $removeContacts);
                return true;
            }
        }

        return false;
    }

    /**
     * "Success" form handler
     *
     * @param Group $entity
     * @param Contact[] $appendContacts
     * @param Contact[] $removeContacts
     */
    protected function onSuccess(Group $entity, array $appendContacts, array $removeContacts)
    {
        $this->appendContacts($entity, $appendContacts);
        $this->removeContacts($entity, $removeContacts);
        $this->manager->persist($entity);
        $this->manager->flush();
    }

    /**
     * Append contacts to group
     *
     * @param Group $group
     * @param Contact[] $contacts
     */
    protected function appendContacts(Group $group, array $contacts)
    {
        /** @var $contact Contact */
        foreach ($contacts as $contact) {
            $contact->addGroup($group);
            $this->manager->persist($contact);
        }
    }

    /**
     * Remove contacts from group
     *
     * @param Group $group
     * @param Contact[] $contacts
     */
    protected function removeContacts(Group $group, array $contacts)
    {
        /** @var $contact Contact */
        foreach ($contacts as $contact) {
            $contact->removeGroup($group);
            $this->manager->persist($contact);
        }
    }
}
