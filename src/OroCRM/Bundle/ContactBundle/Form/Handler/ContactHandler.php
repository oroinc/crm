<?php

namespace OroCRM\Bundle\ContactBundle\Form\Handler;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\UnitOfWork;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

use OroCRM\Bundle\AccountBundle\Entity\Account;
use OroCRM\Bundle\ContactBundle\Entity\Contact;

class ContactHandler
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
     * @var EntityManagerInterface
     */
    protected $manager;

    /**
     * @param FormInterface          $form
     * @param Request                $request
     * @param EntityManagerInterface $manager
     */
    public function __construct(FormInterface $form, Request $request, EntityManagerInterface $manager)
    {
        $this->form    = $form;
        $this->request = $request;
        $this->manager = $manager;
    }

    /**
     * Process form
     *
     * @param  Contact $entity
     *
     * @return bool True on successful processing, false otherwise
     */
    public function process(Contact $entity)
    {
        $this->form->setData($entity);

        if (in_array($this->request->getMethod(), array('POST', 'PUT'))) {
            $this->form->submit($this->request);

            if ($this->form->isValid()) {
                $appendAccounts = $this->form->get('appendAccounts')->getData();
                $removeAccounts = $this->form->get('removeAccounts')->getData();
                $this->onSuccess($entity, $appendAccounts, $removeAccounts);

                return true;
            }
        }

        return false;
    }

    /**
     * "Success" form handler
     *
     * @param Contact $entity
     * @param array $appendAccounts
     * @param array $removeAccounts
     */
    protected function onSuccess(Contact $entity, array $appendAccounts, array $removeAccounts)
    {
        $this->appendAccounts($entity, $appendAccounts);
        $this->removeAccounts($entity, $removeAccounts);

        $this->manager->persist($entity);
        $this->setUpdatedAt($entity);

        $this->manager->flush();
    }

    /**
     * Set updated at to current DateTime when related entities updated
     * TODO: consider refactoring of this feature to make it applicable to all entities
     *
     * @param Contact $entity
     */
    protected function setUpdatedAt(Contact $entity)
    {
        /** @var UnitOfWork $uow */
        $uow = $this->manager->getUnitOfWork();
        $uow->computeChangeSets();

        $isEntityChanged   = count($uow->getEntityChangeSet($entity)) > 0;
        $isRelationChanged = count($uow->getScheduledEntityUpdates()) > 0 ||
            count($uow->getScheduledCollectionUpdates()) > 0 ||
            count($uow->getScheduledCollectionDeletions()) > 0;

        if (false === $isEntityChanged && $isRelationChanged) {
            $entity->setUpdatedAt(new \DateTime('now', new \DateTimeZone('UTC')));
            $this->manager->persist($entity);
        }
    }

    /**
     * Append contacts to account
     *
     * @param Contact $contact
     * @param Account[] $accounts
     */
    protected function appendAccounts(Contact $contact, array $accounts)
    {
        foreach ($accounts as $account) {
            $contact->addAccount($account);
        }
    }

    /**
     * Remove contacts from account
     *
     * @param Contact $contact
     * @param Account[] $accounts
     */
    protected function removeAccounts(Contact $contact, array $accounts)
    {
        foreach ($accounts as $account) {
            $contact->removeAccount($account);
        }
    }
}
