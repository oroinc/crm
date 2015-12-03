<?php

namespace OroCRM\Bundle\ContactBundle\Form\Handler;

use Doctrine\ORM\EntityManagerInterface;

use Oro\Bundle\SecurityBundle\Exception\ForbiddenException;
use Oro\Bundle\SoapBundle\Entity\Manager\ApiEntityManager;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

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
     * @param ContactPhone $entity
     *
     * @return bool True on successful processing, false otherwise
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
                $contact = $this->manager->find(Contact::class, $this->request->request->get('contactId'));
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
     */
    public function handleDelete($id, ApiEntityManager $manager)
    {
        /** @var ContactPhone $contactPhone */
        $contactPhone = $manager->find($id);

        if ($contactPhone->isPrimary() && $contactPhone->getOwner()->getPhones()->count() === 1) {
            $em = $manager->getObjectManager();
            $em->remove($contactPhone);
            $em->flush();
        } else {
            throw new ForbiddenException("Contact has several phones");
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
