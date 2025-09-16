<?php

namespace Oro\Bundle\ContactBundle\Controller;

use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\AddressBundle\Form\Handler\AddressHandler;
use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\ContactBundle\Entity\ContactAddress;
use Oro\Bundle\SecurityBundle\Attribute\AclAncestor;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;

/**
 * The controller for ContactAddress entity.
 */
class ContactAddressController extends AbstractController
{
    #[Route(path: '/address-book/{id}', name: 'oro_contact_address_book', requirements: ['id' => '\d+'])]
    #[Template('@OroContact/ContactAddress/addressBook.html.twig')]
    #[AclAncestor('oro_contact_view')]
    public function addressBookAction(Contact $contact): array
    {
        return [
            'entity' => $contact,
            'address_edit_acl_resource' => 'oro_contact_update'
        ];
    }

    #[Route(
        path: '/{contactId}/address-create',
        name: 'oro_contact_address_create',
        requirements: ['contactId' => '\d+']
    )]
    #[Template('@OroContact/ContactAddress/update.html.twig')]
    #[AclAncestor('oro_contact_create')]
    public function createAction(
        Request $request,
        #[MapEntity(id: 'contactId')]
        Contact $contact
    ): array|RedirectResponse {
        return $this->update($request, $contact, new ContactAddress());
    }

    #[Route(
        path: '/{contactId}/address-update/{id}',
        name: 'oro_contact_address_update',
        requirements: ['contactId' => '\d+', 'id' => '\d+'],
        defaults: ['id' => 0]
    )]
    #[Template('@OroContact/ContactAddress/update.html.twig')]
    #[AclAncestor('oro_contact_update')]
    public function updateAction(
        Request $request,
        #[MapEntity(id: 'contactId')]
        Contact $contact,
        ContactAddress $address
    ): array|RedirectResponse {
        return $this->update($request, $contact, $address);
    }

    /**
     * @throws BadRequestHttpException
     */
    protected function update(Request $request, Contact $contact, ContactAddress $address): array|RedirectResponse
    {
        $responseData = [
            'saved' => false,
            'contact' => $contact
        ];

        if ($request->isMethod('GET') && !$address->getId()) {
            $address->setFirstName($contact->getFirstName());
            $address->setLastName($contact->getLastName());
            if (!$contact->getAddresses()->count()) {
                $address->setPrimary(true);
            }
        }

        if ($address->getOwner() && $address->getOwner()->getId() != $contact->getId()) {
            throw new BadRequestHttpException('Address must belong to contact');
        } elseif (!$address->getOwner()) {
            $contact->addAddress($address);
        }

        // Update contact's modification date when an address is changed
        $contact->setUpdatedAt(new \DateTime('now', new \DateTimeZone('UTC')));

        $form = $this->container->get('oro_contact.contact_address.form');
        if ($this->container->get(AddressHandler::class)->process(
            $address,
            $form,
            $request
        )) {
            $this->container->get('doctrine')->getManager()->flush();
            $responseData['entity'] = $address;
            $responseData['saved'] = true;
        }

        $responseData['form'] = $form->createView();

        return $responseData;
    }

    #[\Override]
    public static function getSubscribedServices(): array
    {
        return array_merge(
            parent::getSubscribedServices(),
            [
                AddressHandler::class,
                'oro_contact.contact_address.form' => Form::class,
                'doctrine' => ManagerRegistry::class,
            ]
        );
    }
}
