<?php

namespace Oro\Bundle\ContactBundle\Form\Handler;

use Doctrine\ORM\EntityManagerInterface;
use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\ContactBundle\Entity\ContactPhone;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * The form handler for ContactPhone entity.
 */
class ContactPhoneHandler
{
    /** @var FormInterface */
    protected $form;

    /** @var RequestStack */
    protected $requestStack;

    /** @var EntityManagerInterface */
    protected $manager;

    /** @var AuthorizationCheckerInterface */
    protected $authorizationChecker;

    public function __construct(
        FormInterface $form,
        RequestStack $requestStack,
        EntityManagerInterface $manager,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        $this->form    = $form;
        $this->requestStack = $requestStack;
        $this->manager = $manager;
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

    protected function onSuccess(ContactPhone $entity, Contact $contact)
    {
        $entity->setOwner($contact);
        $this->manager->persist($entity);
        $this->manager->flush();
    }
}
