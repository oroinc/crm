<?php

namespace OroCRM\Bundle\ContactBundle\Form\Handler;

use Doctrine\ORM\EntityManagerInterface;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

use Oro\Bundle\SoapBundle\Entity\Manager\ApiEntityManager;
use Oro\Bundle\SecurityBundle\SecurityFacade;

use OroCRM\Bundle\ContactBundle\Validator\ContactPhoneDeleteValidator;
use OroCRM\Bundle\ContactBundle\Entity\ContactPhone;
use OroCRM\Bundle\ContactBundle\Entity\Contact;

class ContactPhoneHandler
{
    /** @var FormInterface */
    protected $form;

    /** @var Request */
    protected $request;

    /** @var EntityManagerInterface */
    protected $manager;

    /** @var  ContactPhoneDeleteValidator */
    protected $contactPhoneDeleteValidator;

    /** @var SecurityFacade */
    protected $securityFacade;

    /**
     * @param FormInterface $form
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @param ContactPhoneDeleteValidator $contactPhoneDeleteValidator
     * @param SecurityFacade $securityFacade
     */
    public function __construct(
        FormInterface $form,
        Request $request,
        EntityManagerInterface $manager,
        ContactPhoneDeleteValidator $contactPhoneDeleteValidator,
        SecurityFacade $securityFacade
    ) {
        $this->form    = $form;
        $this->request = $request;
        $this->manager = $manager;
        $this->contactPhoneDeleteValidator = $contactPhoneDeleteValidator;
        $this->securityFacade = $securityFacade;
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

        $submitData = [
            'phone' => $this->request->request->get('phone'),
            'primary' => $this->request->request->get('primary')
        ];

        if (in_array($this->request->getMethod(), ['POST', 'PUT'])) {
            $this->form->submit($submitData);

            if ($this->form->isValid() && $this->request->request->get('contactId')) {
                $contact = $this->manager->find(
                    'OroCRMContactBundle:Contact',
                    $this->request->request->get('contactId')
                );
                if (!$this->securityFacade->isGranted('EDIT', $contact)) {
                    throw new AccessDeniedException();
                }

                if ($contact->getPrimaryPhone() && $this->request->request->get('primary') === true) {
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
        if (!$this->securityFacade->isGranted('EDIT', $contactPhone->getOwner())) {
            throw new AccessDeniedException();
        }

        if ($this->contactPhoneDeleteValidator->validate($contactPhone)) {
            $em = $manager->getObjectManager();
            $em->remove($contactPhone);
            $em->flush();
        } else {
            throw new \Exception("oro.contact.phone.error.delete.more_one", 500);
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
