<?php

namespace OroCRM\Bundle\ContactBundle\Form\Handler;

use Doctrine\ORM\EntityManagerInterface;

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

        if (in_array($this->request->getMethod(), array('POST', 'PUT'))) {
            $this->form->submit($submitData);

            if ($this->form->isValid()) {
                $contact = $this->manager->find(Contact::class, $this->request->request->get('contactId'));
                $this->onSuccess($entity, $contact);

                return true;
            }
        }

        return false;
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
