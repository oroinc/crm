<?php

namespace OroCRM\Bundle\ContactBundle\Controller\Api\Rest;

use Symfony\Component\HttpFoundation\Response;

use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\Rest\Util\Codes;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Oro\Bundle\UserBundle\Annotation\AclAncestor;
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
     * @param string $id
     *
     * @ApiDoc(
     *      description="Get contact address",
     *      resource=true
     * )
     * @AclAncestor("orocrm_contact_view")
     * @return Response
     */
    public function getAction($id)
    {
        return $this->handleGetRequest($id);
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
     * @return Response
     */
    public function cgetAction($contactId)
    {
        /** @var Contact $contact */
        $contact = $this->getContactManager()->find($contactId);
        $items = $contact->getAddresses();
        $result = array();
        foreach ($items as $item) {
            $result[] = $this->getPreparedItem($item);
        }
        unset($items);

        return new Response(json_encode($result), $result ? Codes::HTTP_OK : Codes::HTTP_NOT_FOUND);
    }

    /**
     * REST DELETE address
     *
     * @ApiDoc(
     *      description="Delete address items",
     *      resource=true
     * )
     * @AclAncestor("orocrm_contact_view")
     * @param int $id
     * @return Response
     */
    public function deleteAction($id)
    {
        return $this->handleDeleteRequest($id);
    }

    /**
     * REST GET address by type
     *
     * @param string $id
     * @param string $typeName
     *
     * @ApiDoc(
     *      description="Get contact address by type",
     *      resource=true
     * )
     * @AclAncestor("orocrm_contact_view")
     * @return Response
     */
    public function getByTypeAction($id, $typeName)
    {
        /** @var Contact $contact */
        $contact = $this->getContactManager()->find($id);

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
     * @param string $id
     *
     * @ApiDoc(
     *      description="Get contact primary address",
     *      resource=true
     * )
     * @AclAncestor("orocrm_contact_view")
     * @return Response
     */
    public function getPrimaryAction($id)
    {
        /** @var Contact $contact */
        $contact = $this->getContactManager()->find($id);

        if ($contact) {
            $address = $contact->getPrimaryAddress();
        } else {
            $address = null;
        }

        $responseData = $address ? json_encode($this->getPreparedItem($address)) : '';

        return new Response($responseData, $address ? Codes::HTTP_OK : Codes::HTTP_NOT_FOUND);
    }

    public function getContactManager()
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
    protected function getPreparedItem($entity)
    {
        // convert addresses to plain array
        $addressTypesData = array();

        /** @var $entity ContactAddress */
        foreach ($entity->getTypes() as $addressType) {
            $addressTypesData[] = parent::getPreparedItem($addressType);
        }

        $result = parent::getPreparedItem($entity);
        $result['types'] = $addressTypesData;

        unset($result['owner']);

        return $result;
    }
}
