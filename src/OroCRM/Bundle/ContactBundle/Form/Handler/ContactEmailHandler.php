<?php

namespace OroCRM\Bundle\ContactBundle\Form\Handler;

use Doctrine\ORM\EntityManagerInterface;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

use Oro\Bundle\SoapBundle\Entity\Manager\ApiEntityManager;

use OroCRM\Bundle\ContactBundle\Validator\ContactEmailDeleteValidator;
use OroCRM\Bundle\ContactBundle\Entity\ContactEmail;
use OroCRM\Bundle\ContactBundle\Entity\Contact;

class ContactEmailHandler
{
    /** @var FormInterface */
    protected $form;

    /** @var Request */
    protected $request;

    /** @var EntityManagerInterface */
    protected $manager;

    /** @var  ContactEmailDeleteValidator */
    protected $contactEmailDeleteValidator;

    /**
     * @param FormInterface $form
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @param ContactEmailDeleteValidator $contactEmailDeleteValidator
     */
    public function __construct(
        FormInterface $form,
        Request $request,
        EntityManagerInterface $manager,
        ContactEmailDeleteValidator $contactEmailDeleteValidator
    ) {
        $this->form    = $form;
        $this->request = $request;
        $this->manager = $manager;
        $this->contactEmailDeleteValidator = $contactEmailDeleteValidator;
    }

    /**
     * Process form
     *
     * @param ContactEmail $entity
     *
     * @return bool True on successful processing, false otherwise
     */
    public function process(ContactEmail $entity)
    {
        $this->form->setData($entity);

        $submitData = [
            'email' => $this->request->request->get('email'),
            'primary' => $this->request->request->get('primary')
        ];

        if (in_array($this->request->getMethod(), ['POST', 'PUT'])) {
            $this->form->submit($submitData);

            if ($this->form->isValid() && $this->request->request->get('contactId')) {
                $contact = $this->manager->find(Contact::class, $this->request->request->get('contactId'));
                if ($contact->getPrimaryEmail() && $this->request->request->get('primary') === true) {
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

        if ($this->contactEmailDeleteValidator->validate($contactEmail)) {
            $em = $manager->getObjectManager();
            $em->remove($contactEmail);
            $em->flush();
        } else {
            throw new \Exception("oro.contact.email.error.delete.more_one", 500);
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
