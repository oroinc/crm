<?php

namespace Oro\Bundle\ContactBundle\Controller\Api\Soap;

use Symfony\Component\Form\FormInterface;
use BeSimple\SoapBundle\ServiceDefinition\Annotation as Soap;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SoapBundle\Controller\Api\Soap\SoapController;
use Oro\Bundle\SoapBundle\Entity\Manager\ApiEntityManager;
use Oro\Bundle\SoapBundle\Form\Handler\ApiFormHandler;

use Oro\Bundle\ContactBundle\Entity\Contact;

class ContactController extends SoapController
{
    /**
     * @Soap\Method("getContacts")
     * @Soap\Param("page", phpType="int")
     * @Soap\Param("limit", phpType="int")
     * @Soap\Result(phpType = "Oro\Bundle\ContactBundle\Entity\Contact[]")
     * @AclAncestor("oro_contact_view")
     */
    public function cgetAction($page = 1, $limit = 10)
    {
        return $this->handleGetListRequest($page, $limit);
    }

    /**
     * @Soap\Method("getContact")
     * @Soap\Param("id", phpType = "int")
     * @Soap\Result(phpType = "Oro\Bundle\ContactBundle\Entity\Contact")
     * @AclAncestor("oro_contact_view")
     */
    public function getAction($id)
    {
        return $this->handleGetRequest($id);
    }

    /**
     * @Soap\Method("getContactAddressByTypeName")
     * @Soap\Param("id", phpType = "int")
     * @Soap\Param("typeName", phpType = "string")
     * @Soap\Result(phpType = "Oro\Bundle\ContactBundle\Entity\ContactAddress")
     * @AclAncestor("oro_contact_view")
     */
    public function getAddressByTypeNameAction($id, $typeName)
    {
        /** @var Contact $contact */
        $contact = $this->getEntity($id);
        $address = $contact->getAddressByTypeName($typeName);

        if (!$address) {
            throw new \SoapFault('NOT_FOUND', sprintf('Contact address with type "%s" can not be found', $typeName));
        }

        return $address;
    }

    /**
     * @Soap\Method("getContactPrimaryAddress")
     * @Soap\Param("id", phpType = "int")
     * @Soap\Result(phpType = "Oro\Bundle\ContactBundle\Entity\ContactAddress")
     * @AclAncestor("oro_contact_view")
     */
    public function getPrimaryAddressAction($id)
    {
        /** @var Contact $contact */
        $contact = $this->getEntity($id);
        $address = $contact->getPrimaryAddress();

        if (!$address) {
            throw new \SoapFault('NOT_FOUND', sprintf('Contact has no primary address', $address));
        }

        return $address;
    }

    /**
     * @Soap\Method("createContact")
     * @Soap\Param("contact", phpType = "Oro\Bundle\ContactBundle\Entity\Contact")
     * @Soap\Result(phpType = "int")
     * @AclAncestor("oro_contact_create")
     */
    public function createAction($contact)
    {
        return $this->handleCreateRequest();
    }

    /**
     * @Soap\Method("updateContact")
     * @Soap\Param("id", phpType = "int")
     * @Soap\Param("contact", phpType = "Oro\Bundle\ContactBundle\Entity\Contact")
     * @Soap\Result(phpType = "boolean")
     * @AclAncestor("oro_contact_update")
     */
    public function updateAction($id, $contact)
    {
        return $this->handleUpdateRequest($id);
    }

    /**
     * @Soap\Method("deleteContact")
     * @Soap\Param("id", phpType = "int")
     * @Soap\Result(phpType = "boolean")
     * @AclAncestor("oro_contact_delete")
     */
    public function deleteAction($id)
    {
        return $this->handleDeleteRequest($id);
    }

    /**
     * @return ApiEntityManager
     */
    public function getManager()
    {
        return $this->container->get('oro_contact.contact.manager.api');
    }

    /**
     * @return FormInterface
     */
    public function getForm()
    {
        return $this->container->get('oro_contact.form.contact.api');
    }

    /**
     * @return ApiFormHandler
     */
    public function getFormHandler()
    {
        return $this->container->get('oro_contact.form.handler.contact.api');
    }
}
