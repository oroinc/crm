<?php

namespace OroCRM\Bundle\ContactBundle\Form\Handler;

use Doctrine\ORM\EntityManagerInterface;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

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
