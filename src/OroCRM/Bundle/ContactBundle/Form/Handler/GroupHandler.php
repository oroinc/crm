<?php

namespace OroCRM\Bundle\ContactBundle\Form\Handler;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

use Doctrine\Common\Persistence\ObjectManager;

use OroCRM\Bundle\ContactBundle\Entity\Contact;
use OroCRM\Bundle\ContactBundle\Entity\Group;

class GroupHandler
{
    /**
     * @var FormInterface
     */
    protected $form;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var ObjectManager
     */
    protected $manager;

    /**
     * @param FormInterface $form
     * @param Request       $request
     * @param ObjectManager $manager
     */
    public function __construct(FormInterface $form, Request $request, ObjectManager $manager)
    {
        $this->form    = $form;
        $this->request = $request;
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

        if (in_array($this->request->getMethod(), array('POST', 'PUT'))) {
            $this->form->submit($this->request);

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
