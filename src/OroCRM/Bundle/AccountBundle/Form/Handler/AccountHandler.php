<?php

namespace OroCRM\Bundle\AccountBundle\Form\Handler;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

use Doctrine\Common\Persistence\ObjectManager;
use OroCRM\Bundle\AccountBundle\Entity\Account;
use OroCRM\Bundle\ContactBundle\Entity\Contact;

class AccountHandler
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
     *
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
     * @param  Account $entity
     * @return bool True on successful processing, false otherwise
     */
    public function process(Account $entity)
    {
        $this->form->setData($entity);

        if (in_array($this->request->getMethod(), array('POST', 'PUT'))) {
            $this->form->bind($this->request);

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
     * @param Account $entity
     * @param array $appendContacts
     * @param array $removeContacts
     */
    protected function onSuccess(Account $entity, array $appendContacts, array $removeContacts)
    {
        $this->appendContacts($entity, $appendContacts);
        $this->removeContacts($entity, $removeContacts);
        $this->manager->persist($entity);
        $this->manager->flush();
    }

    /**
     * Append contacts to account
     *
     * @param Account $account
     * @param Contact[] $contacts
     */
    protected function appendContacts(Account $account, array $contacts)
    {
        foreach ($contacts as $contact) {
            $account->addContact($contact);
        }
    }

    /**
     * Remove contacts from account
     *
     * @param Account $account
     * @param Contact[] $contacts
     */
    protected function removeContacts(Account $account, array $contacts)
    {
        foreach ($contacts as $contact) {
            $account->removeContact($contact);
        }
    }
}
