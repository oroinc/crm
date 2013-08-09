<?php

namespace OroCRM\Bundle\ContactBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

use Oro\Bundle\SoapBundle\Entity\Manager\ApiEntityManager;
use Oro\Bundle\UserBundle\Annotation\Acl;
use OroCRM\Bundle\ContactBundle\Entity\ContactAddress;
use OroCRM\Bundle\ContactBundle\Entity\Contact;

/**
 * @Acl(
 *      id="orocrm_contact_address",
 *      name="Contact address manipulation",
 *      description="Contact address manipulation",
 *      parent="orocrm_contact"
 * )
 */
class ContactAddressController extends Controller
{
    /**
     * @Route("/address-book/{id}", name="orocrm_contact_address_book", requirements={"id"="\d+"})
     * @Template
     * @Acl(
     *      id="orocrm_contact_address_book",
     *      name="View Contact Address Book",
     *      description="View contact Address Book",
     *      parent="orocrm_contact_address"
     * )
     */
    public function addressBookAction(Contact $contact)
    {
        return array(
            'entity' => $contact
        );
    }

    /**
     * @Route(
     *      "/{contactId}/address-create",
     *      name="orocrm_contact_address_create",
     *      requirements={"contactId"="\d+"}
     * )
     * @Template("OroCRMContactBundle:ContactAddress:update.html.twig")
     * @Acl(
     *      id="orocrm_contact_address_create",
     *      name="Create Contact Address",
     *      description="Create Contact Address",
     *      parent="orocrm_contact_address"
     * )
     * @ParamConverter("contact", options={"id" = "contactId"})
     */
    public function createAction(Contact $contact)
    {
        return $this->updateAction($contact, new ContactAddress());
    }

    /**
     * @Route(
     *      "/{contactId}/address-update/{id}",
     *      name="orocrm_contact_address_update",
     *      requirements={"contactId"="\d+","id"="\d+"},defaults={"id"=0}
     * )
     * @Template
     * @Acl(
     *      id="orocrm_contact_address_update",
     *      name="Update Contact Address",
     *      description="Update Contact Address",
     *      parent="orocrm_contact_address"
     * )
     * @ParamConverter("contact", options={"id" = "contactId"})
     */
    public function updateAction(Contact $contact, ContactAddress $address)
    {
        $responseData = array(
            'saved' => false,
            'contact' => $contact
        );

        if ($this->getRequest()->getMethod() == 'GET' && !$address->getId()) {
            $address->setFirstName($contact->getFirstName());
            $address->setLastName($contact->getLastName());
        }

        if ($address->getOwner() && $address->getOwner()->getId() != $contact->getId()) {
            throw new BadRequestHttpException('Address must belong to contact');
        } elseif (!$address->getOwner()) {
            $contact->addAddress($address);
        }

        if (!$contact->getPrimaryAddress()) {
            $this->getFlashBag()->add('error', 'Contact must have one primary address');
        } elseif ($this->get('orocrm_contact.form.handler.contact_address')->process($address)) {
            $this->getDoctrine()->getManager()->flush();
            $responseData['entity'] = $address;
            $responseData['saved'] = true;
        }

        $responseData['form'] = $this->get('orocrm_contact.contact_address.form')->createView();
        return $responseData;
    }

    /**
     * @return FlashBag
     */
    protected function getFlashBag()
    {
        return $this->get('session')->getFlashBag();
    }

    /**
     * @return ApiEntityManager
     */
    protected function getManager()
    {
        return $this->get('orocrm_contact.contact.manager');
    }
}
