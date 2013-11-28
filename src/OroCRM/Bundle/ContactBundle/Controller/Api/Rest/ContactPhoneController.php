<?php

namespace OroCRM\Bundle\ContactBundle\Controller\Api\Rest;

use Symfony\Component\HttpFoundation\Response;

use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\Rest\Util\Codes;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SoapBundle\Controller\Api\Rest\RestController;
use OroCRM\Bundle\ContactBundle\Entity\Contact;
use OroCRM\Bundle\ContactBundle\Entity\ContactPhone;

/**
 * @RouteResource("phone")
 * @NamePrefix("oro_api_")
 */
class ContactPhoneController extends RestController implements ClassResourceInterface
{
    /**
     * REST GET list
     *
     * @ApiDoc(
     *      description="Get all phones items",
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
        $items = $contact->getPhones();
        $result = array();
        foreach ($items as $item) {
            $result[] = $this->getPreparedItem($item);
        }
        unset($items);

        return new Response(json_encode($result), Codes::HTTP_OK);
    }

    /**
     * REST GET primary phone
     *
     * @param string $contactId
     *
     * @ApiDoc(
     *      description="Get contact primary phone",
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
            $phone = $contact->getPrimaryPhone();
        } else {
            $phone = null;
        }

        $responseData = $phone ? json_encode($this->getPreparedItem($phone)) : '';

        return new Response($responseData, $phone ? Codes::HTTP_OK : Codes::HTTP_NOT_FOUND);
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
        return $this->get('orocrm_contact.contact_phone.manager.api');
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
        $result['id']      = $entity->getId();
        $result['owner']   = (string) $entity->getOwner();
        $result['phone']   = $entity->getPhone();
        $result['primary'] = $entity->isPrimary();
        
        return $result;
    }
}
