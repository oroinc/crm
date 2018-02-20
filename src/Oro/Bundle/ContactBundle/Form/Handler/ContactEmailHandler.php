<?php

namespace Oro\Bundle\ContactBundle\Form\Handler;

use Doctrine\ORM\EntityManagerInterface;
use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\ContactBundle\Entity\ContactEmail;
use Oro\Bundle\ContactBundle\Validator\ContactEmailDeleteValidator;
use Oro\Bundle\SoapBundle\Entity\Manager\ApiEntityManager;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ContactEmailHandler
{
    /** @var FormInterface */
    protected $form;

    /** @var RequestStack */
    protected $requestStack;

    /** @var EntityManagerInterface */
    protected $manager;

    /** @var  ContactEmailDeleteValidator */
    protected $contactEmailDeleteValidator;

    /** @var AuthorizationCheckerInterface */
    protected $authorizationChecker;

    /**
     * @param FormInterface $form
     * @param RequestStack $requestStack
     * @param EntityManagerInterface $manager
     * @param ContactEmailDeleteValidator $contactEmailDeleteValidator
     * @param AuthorizationCheckerInterface $authorizationChecker
     */
    public function __construct(
        FormInterface $form,
        RequestStack $requestStack,
        EntityManagerInterface $manager,
        ContactEmailDeleteValidator $contactEmailDeleteValidator,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        $this->form = $form;
        $this->requestStack = $requestStack;
        $this->manager = $manager;
        $this->contactEmailDeleteValidator = $contactEmailDeleteValidator;
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * Process form
     *
     * @param ContactEmail $entity
     *
     * @return bool True on successful processing, false otherwise
     *
     * @throws AccessDeniedException
     */
    public function process(ContactEmail $entity)
    {
        $this->form->setData($entity);

        $request = $this->requestStack->getCurrentRequest();
        $submitData = [
            'email' => $request->request->get('email'),
            'primary' => $request->request->get('primary')
        ];

        if (in_array($request->getMethod(), ['POST', 'PUT'], true)) {
            $this->form->submit($submitData);

            if ($this->form->isValid() && $request->request->get('contactId')) {
                $contact = $this->manager->find(
                    'OroContactBundle:Contact',
                    $request->request->get('contactId')
                );
                if (!$this->authorizationChecker->isGranted('EDIT', $contact)) {
                    throw new AccessDeniedException();
                }

                if ($contact->getPrimaryEmail() && $request->request->get('primary') === true) {
                    return false;
                }

                $this->onSuccess($entity, $contact);

                return true;
            }
        }

        return false;
    }

    /**
     * @param $id
     * @param ApiEntityManager $manager
     * @throws \Exception
     */
    public function handleDelete($id, ApiEntityManager $manager)
    {
        /** @var ContactEmail $contactEmail */
        $contactEmail = $manager->find($id);
        if (!$this->authorizationChecker->isGranted('EDIT', $contactEmail->getOwner())) {
            throw new AccessDeniedException();
        }

        if ($this->contactEmailDeleteValidator->validate($contactEmail)) {
            $em = $manager->getObjectManager();
            $em->remove($contactEmail);
            $em->flush();
        }
    }

    /**
     * @param ContactEmail $entity
     * @param Contact $contact
     */
    protected function onSuccess(ContactEmail $entity, Contact $contact)
    {
        $entity->setOwner($contact);
        $this->manager->persist($entity);
        $this->manager->flush();
    }
}
