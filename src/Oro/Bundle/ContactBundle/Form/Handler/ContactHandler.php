<?php

namespace Oro\Bundle\ContactBundle\Form\Handler;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\UnitOfWork;
use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\ContactBundle\Entity\Contact;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class ContactHandler
{
    /**
     * @var string
     */
    protected $formType;

    /**
     * @var FormFactoryInterface
     */
    protected $formFactory;

    /**
     * @var RequestStack
     */
    protected $requestStack;

    /**
     * @var EntityManagerInterface
     */
    protected $manager;

    /**
     * @var FormInterface|null
     */
    private $form;

    /**
     * @param FormFactoryInterface $formFactory
     * @param RequestStack $requestStack
     * @param EntityManagerInterface $manager
     * @param string $formType
     */
    public function __construct(
        FormFactoryInterface $formFactory,
        RequestStack $requestStack,
        EntityManagerInterface $manager,
        string $formType
    ) {
        $this->formFactory = $formFactory;
        $this->requestStack = $requestStack;
        $this->manager = $manager;
        $this->formType = $formType;
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
        $request = $this->requestStack->getCurrentRequest();

        if (in_array($request->getMethod(), ['POST', 'PUT'], true)) {
            $this->form = $this->formFactory->create($this->formType, $entity, ['method' => $request->getMethod()]);
            $this->form->handleRequest($request);

            if ($this->form->isValid()) {
                $appendAccounts = $this->form->get('appendAccounts')->getData();
                $removeAccounts = $this->form->get('removeAccounts')->getData();
                $this->onSuccess($entity, $appendAccounts, $removeAccounts);

                return true;
            }
        } else {
            $this->form = $this->formFactory->create($this->formType, $entity);
        }

        return false;
    }

    /**
     * @return null|FormInterface
     */
    public function getForm()
    {
        return $this->form;
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
