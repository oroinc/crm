<?php

namespace Oro\Bundle\ContactBundle\Controller\Api\Rest;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\ContactBundle\Entity\ContactAddress;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SoapBundle\Controller\Api\Rest\RestController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * REST API CRUD controller for ContactAddress entity.
 */
class ContactAddressController extends RestController
{
    /**
     * REST GET address
     *
     * @param int $contactId
     * @param int $addressId
     *
     * @ApiDoc(
     *      description="Get contact address",
     *      resource=true
     * )
     * @AclAncestor("oro_contact_view")
     * @return Response
     */
    public function getAction(int $contactId, int $addressId)
    {
        /** @var Contact $contact */
        $contact = $this->getContactManager()->find($contactId);

        /** @var ContactAddress $address */
        $address = $this->getManager()->find($addressId);

        $addressData = null;
        if ($address && $contact->getAddresses()->contains($address)) {
            $addressData = $this->getPreparedItem($address);
        }
        $responseData = $addressData ? json_encode($addressData) : '';
        return new Response($responseData, $address ? Response::HTTP_OK : Response::HTTP_NOT_FOUND);
    }

    /**
     * REST GET list
     *
     * @ApiDoc(
     *      description="Get all addresses items",
     *      resource=true
     * )
     * @AclAncestor("oro_contact_view")
     * @param int $contactId
     *
     * @return JsonResponse
     */
    public function cgetAction($contactId)
    {
        /** @var Contact $contact */
        $contact = $this->getContactManager()->find($contactId);
        $result  = [];

        if (!empty($contact)) {
            $items = $contact->getAddresses();

            foreach ($items as $item) {
                $result[] = $this->getPreparedItem($item);
            }
        }

        return new JsonResponse(
            $result,
            empty($contact) ? Response::HTTP_NOT_FOUND : Response::HTTP_OK
        );
    }

    /**
     * REST DELETE address
     *
     * @ApiDoc(
     *      description="Delete address items",
     *      resource=true
     * )
     * @AclAncestor("oro_contact_delete")
     * @param int $contactId
     * @param int $addressId
     *
     * @return Response
     */
    public function deleteAction(int $contactId, int $addressId)
    {
        /** @var ContactAddress $address */
        $address = $this->getManager()->find($addressId);
        /** @var Contact $contact */
        $contact = $this->getContactManager()->find($contactId);
        if ($contact->getAddresses()->contains($address)) {
            $contact->removeAddress($address);
            // Update contact's modification date when an address is removed
            $contact->setUpdatedAt(new \DateTime('now', new \DateTimeZone('UTC')));
            return $this->handleDeleteRequest($addressId);
        } else {
            return $this->handleView($this->view(null, Response::HTTP_NOT_FOUND));
        }
    }

    /**
     * REST GET address by type
     *
     * @param int $contactId
     * @param string $typeName
     *
     * @ApiDoc(
     *      description="Get contact address by type",
     *      resource=true
     * )
     * @AclAncestor("oro_contact_view")
     * @return Response
     */
    public function getByTypeAction(int $contactId, $typeName)
    {
        /** @var Contact $contact */
        $contact = $this->getContactManager()->find($contactId);

        if ($contact) {
            $address = $contact->getAddressByTypeName($typeName);
        } else {
            $address = null;
        }

        $responseData = $address ? json_encode($this->getPreparedItem($address)) : '';

        return new Response($responseData, $address ? Response::HTTP_OK : Response::HTTP_NOT_FOUND);
    }

    /**
     * REST GET primary address
     *
     * @param int $contactId
     *
     * @ApiDoc(
     *      description="Get contact primary address",
     *      resource=true
     * )
     * @AclAncestor("oro_contact_view")
     * @return Response
     */
    public function getPrimaryAction(int $contactId)
    {
        /** @var Contact $contact */
        $contact = $this->getContactManager()->find($contactId);

        if ($contact) {
            $address = $contact->getPrimaryAddress();
        } else {
            $address = null;
        }

        $responseData = $address ? json_encode($this->getPreparedItem($address)) : '';

        return new Response($responseData, $address ? Response::HTTP_OK : Response::HTTP_NOT_FOUND);
    }

    protected function getContactManager()
    {
        return $this->get('oro_contact.contact.manager.api');
    }

    /**
     * {@inheritdoc}
     */
    public function getManager()
    {
        return $this->get('oro_contact.contact_address.manager.api');
    }

    /**
     * {@inheritdoc}
     */
    public function getForm()
    {
        throw new \BadMethodCallException('Form is not available.');
    }

    /**
     * {@inheritdoc}
     */
    public function getFormHandler()
    {
        throw new \BadMethodCallException('FormHandler is not available.');
    }

    /**
     * {@inheritDoc}
     */
    protected function getPreparedItem($entity, $resultFields = [])
    {
        // convert addresses to plain array
        $addressTypesData = array();

        /** @var $entity ContactAddress */
        foreach ($entity->getTypes() as $addressType) {
            $addressTypesData[] = parent::getPreparedItem($addressType);
        }

        $result                = parent::getPreparedItem($entity);
        $result['types']       = $addressTypesData;
        $result['countryIso2'] = $entity->getCountryIso2();
        $result['countryIso3'] = $entity->getCountryIso2();
        $result['regionCode']  = $entity->getRegionCode();
        $result['country'] = $entity->getCountryName();

        unset($result['owner']);

        return $result;
    }
}
