<?php

namespace Oro\Bundle\ContactBundle\Form\Handler;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\UnitOfWork;
use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\FormBundle\Form\Handler\RequestHandlerTrait;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class ContactHandler
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
     * @var EntityManagerInterface
     */
    protected $manager;

    public function __construct(FormInterface $form, RequestStack $requestStack, EntityManagerInterface $manager)
    {
        $this->form    = $form;
        $this->requestStack = $requestStack;
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

        $request = $this->requestStack->getCurrentRequest();
        if (in_array($request->getMethod(), ['POST', 'PUT'], true)) {
            $this->submitPostPutRequest($this->form, $request);

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
