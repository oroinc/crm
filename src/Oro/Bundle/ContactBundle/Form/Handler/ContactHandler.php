<?php

namespace Oro\Bundle\ContactBundle\Form\Handler;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\UnitOfWork;
use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\FormBundle\Form\Handler\FormHandlerInterface;
use Oro\Bundle\FormBundle\Form\Handler\RequestHandlerTrait;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * The handler for Contact form.
 */
class ContactHandler implements FormHandlerInterface
{
    use RequestHandlerTrait;

    protected EntityManagerInterface $manager;

    public function __construct(EntityManagerInterface $manager)
    {
        $this->manager = $manager;
    }

    /**
     * {@inheritDoc}
     */
    public function process($entity, FormInterface $form, Request $request)
    {
        $form->setData($entity);
        if (\in_array($request->getMethod(), ['POST', 'PUT'], true)) {
            $this->submitPostPutRequest($form, $request);

            if ($form->isValid()) {
                $appendAccounts = $form->get('appendAccounts')->getData();
                $removeAccounts = $form->get('removeAccounts')->getData();
                $this->onSuccess($entity, $appendAccounts, $removeAccounts);

                return true;
            }
        }

        return false;
    }

    /**
     * "Success" form handler
     */
    protected function onSuccess(Contact $entity, array $appendAccounts, array $removeAccounts): void
    {
        $this->appendAccounts($entity, $appendAccounts);
        $this->removeAccounts($entity, $removeAccounts);

        $this->manager->persist($entity);
        $this->setUpdatedAt($entity);

        $this->manager->flush();
    }

    /**
     * Set updated at to current DateTime when related entities updated
     */
    protected function setUpdatedAt(Contact $entity): void
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
    protected function appendAccounts(Contact $contact, array $accounts): void
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
    protected function removeAccounts(Contact $contact, array $accounts): void
    {
        foreach ($accounts as $account) {
            $contact->removeAccount($account);
        }
    }
}
