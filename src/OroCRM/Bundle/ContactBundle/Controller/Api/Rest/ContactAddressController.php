<?php

namespace OroCRM\Bundle\ContactBundle\Controller\Api\Rest;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\RestBundle\Util\Codes;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SoapBundle\Controller\Api\Rest\RestController;
use OroCRM\Bundle\ContactBundle\Entity\Contact;
use OroCRM\Bundle\ContactBundle\Entity\ContactAddress;

/**
 * @RouteResource("address")
 * @NamePrefix("oro_api_")
 */
class ContactAddressController extends RestController implements ClassResourceInterface
{
    /**
     * REST GET address
     *
     * @param string $contactId
     * @param string $addressId
     *
     * @ApiDoc(
     *      description="Get contact address",
     *      resource=true
     * )
     * @AclAncestor("orocrm_contact_view")
     * @return Response
     */
    public function getAction($contactId, $addressId)
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
        return new Response($responseData, $address ? Codes::HTTP_OK : Codes::HTTP_NOT_FOUND);
    }

    /**
     * REST GET list
     *
     * @ApiDoc(
     *      description="Get all addresses items",
     *      resource=true
     * )
     * @AclAncestor("orocrm_contact_view")
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
            empty($contact) ? Codes::HTTP_NOT_FOUND : Codes::HTTP_OK
        );
    }

    /**
     * REST DELETE address
     *
     * @ApiDoc(
     *      description="Delete address items",
     *      resource=true
     * )
     * @AclAncestor("orocrm_contact_delete")
     * @param     $contactId
     * @param int $addressId
     *
     * @return Response
     */
    public function deleteAction($contactId, $addressId)
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
            return $this->handleView($this->view(null, Codes::HTTP_NOT_FOUND));
        }
    }

    /**
     * REST GET address by type
     *
     * @param string $contactId
     * @param string $typeName
     *
     * @ApiDoc(
     *      description="Get contact address by type",
     *      resource=true
     * )
     * @AclAncestor("orocrm_contact_view")
     * @return Response
     */
    public function getByTypeAction($contactId, $typeName)
    {
        /** @var Contact $contact */
        $contact = $this->getContactManager()->find($contactId);

        if ($contact) {
            $address = $contact->getAddressByTypeName($typeName);
        } else {
            $address = null;
        }

        $responseData = $address ? json_encode($this->getPreparedItem($address)) : '';

        return new Response($responseData, $address ? Codes::HTTP_OK : Codes::HTTP_NOT_FOUND);
    }

    /**
     * REST GET primary address
     *
     * @param string $contactId
     *
     * @ApiDoc(
     *      description="Get contact primary address",
     *      resource=true
     * )
     * @AclAncestor("orocrm_contact_view")
     * @return Response
     */
    public function getPrimaryAction($contactId)
    {
        /** @var Contact $contact */
        $contact = $this->getContactManager()->find($contactId);

        if ($contact) {
            $address = $contact->getPrimaryAddress();
        } else {
            $address = null;
        }

        $responseData = $address ? json_encode($this->getPreparedItem($address)) : '';

        return new Response($responseData, $address ? Codes::HTTP_OK : Codes::HTTP_NOT_FOUND);
    }

    protected function getContactManager()
    {
        return $this->get('orocrm_contact.contact.manager.api');
    }

    /**
     * {@inheritdoc}
     */
    public function getManager()
    {
        return $this->get('orocrm_contact.contact_address.manager.api');
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
