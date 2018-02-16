<?php

namespace Oro\Bundle\ContactBundle\Form\Handler;

use Doctrine\ORM\EntityManagerInterface;
use Oro\Bundle\SoapBundle\Entity\Manager\ApiEntityManager;
use Oro\Bundle\ContactBundle\Validator\ContactPhoneDeleteValidator;
use Oro\Bundle\ContactBundle\Entity\ContactPhone;
use Oro\Bundle\ContactBundle\Entity\Contact;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ContactPhoneHandler
{
    /** @var FormInterface */
    protected $form;

    /** @var RequestStack */
    protected $requestStack;

    /** @var EntityManagerInterface */
    protected $manager;

    /** @var  ContactPhoneDeleteValidator */
    protected $contactPhoneDeleteValidator;

    /** @var AuthorizationCheckerInterface */
    protected $authorizationChecker;

    /**
     * @param FormInterface $form
     * @param RequestStack $requestStack
     * @param EntityManagerInterface $manager
     * @param ContactPhoneDeleteValidator $contactPhoneDeleteValidator
     * @param AuthorizationCheckerInterface $authorizationChecker
     */
    public function __construct(
        FormInterface $form,
        RequestStack $requestStack,
        EntityManagerInterface $manager,
        ContactPhoneDeleteValidator $contactPhoneDeleteValidator,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        $this->form    = $form;
        $this->requestStack = $requestStack;
        $this->manager = $manager;
        $this->contactPhoneDeleteValidator = $contactPhoneDeleteValidator;
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * Process form
     *
     * @param ContactPhone $entity
     *
     * @return bool True on successful processing, false otherwise
     *
     * @throws AccessDeniedException
     */
    public function process(ContactPhone $entity)
    {
        $this->form->setData($entity);

        $request = $this->requestStack->getCurrentRequest();
        $submitData = [
            'phone' => $request->request->get('phone'),
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

                if ($contact->getPrimaryPhone() && $request->request->get('primary') === true) {
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
     *
     * @throws \Exception
     */
    public function handleDelete($id, ApiEntityManager $manager)
    {
        /** @var ContactPhone $contactPhone */
        $contactPhone = $manager->find($id);
        if (!$this->authorizationChecker->isGranted('EDIT', $contactPhone->getOwner())) {
            throw new AccessDeniedException();
        }

        if ($this->contactPhoneDeleteValidator->validate($contactPhone)) {
            $em = $manager->getObjectManager();
            $em->remove($contactPhone);
            $em->flush();
        }
    }

    /**
     * @param ContactPhone $entity
     * @param Contact $contact
     */
    protected function onSuccess(ContactPhone $entity, Contact $contact)
    {
        $entity->setOwner($contact);
        $this->manager->persist($entity);
        $this->manager->flush();
    }
}
