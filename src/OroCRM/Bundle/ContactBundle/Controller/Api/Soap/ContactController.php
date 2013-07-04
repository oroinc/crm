<?php

namespace OroCRM\Bundle\ContactBundle\Controller\Api\Soap;

use Symfony\Component\Form\FormInterface;
use BeSimple\SoapBundle\ServiceDefinition\Annotation as Soap;

use Oro\Bundle\SoapBundle\Controller\Api\Soap\FlexibleSoapController;
use Oro\Bundle\SoapBundle\Entity\Manager\ApiFlexibleEntityManager;
use Oro\Bundle\SoapBundle\Form\Handler\ApiFormHandler;

use OroCRM\Bundle\ContactBundle\Entity\Contact;

class ContactController extends FlexibleSoapController
{
    /**
     * @Soap\Method("getContacts")
     * @Soap\Param("page", phpType="int")
     * @Soap\Param("limit", phpType="int")
     * @Soap\Result(phpType = "OroCRM\Bundle\ContactBundle\Entity\Contact[]")
     */
    public function cgetAction($page = 1, $limit = 10)
    {
        return $this->handleGetListRequest($page, $limit);
    }

    /**
     * @Soap\Method("getContact")
     * @Soap\Param("id", phpType = "int")
     * @Soap\Result(phpType = "OroCRM\Bundle\ContactBundle\Entity\Contact")
     */
    public function getAction($id)
    {
        return $this->handleGetRequest($id);
    }

    /**
     * @Soap\Method("getContactAddressByTypeName")
     * @Soap\Param("id", phpType = "int")
     * @Soap\Param("typeName", phpType = "string")
     * @Soap\Result(phpType = "Oro\Bundle\AddressBundle\Entity\AddressType")
     */
    public function getAddressByTypeNameAction($id, $typeName)
    {
        /** @var Contact $contact */
        $contact = $this->getEntity($id);
        return $contact->getAddressByTypeName($typeName);
    }

    /**
     * @Soap\Method("createContact")
     * @Soap\Param("contact", phpType = "OroCRM\Bundle\ContactBundle\Entity\ContactSoap")
     * @Soap\Result(phpType = "boolean")
     */
    public function createAction($contact)
    {
        return $this->handleCreateRequest();
    }

    /**
     * @Soap\Method("updateContact")
     * @Soap\Param("id", phpType = "int")
     * @Soap\Param("contact", phpType = "OroCRM\Bundle\ContactBundle\Entity\ContactSoap")
     * @Soap\Result(phpType = "boolean")
     */
    public function updateAction($id, $contact)
    {
        return $this->handleUpdateRequest($id);
    }

    /**
     * @Soap\Method("deleteContact")
     * @Soap\Param("id", phpType = "int")
     * @Soap\Result(phpType = "boolean")
     */
    public function deleteAction($id)
    {
        return $this->handleDeleteRequest($id);
    }

    /**
     * @return ApiFlexibleEntityManager
     */
    public function getManager()
    {
        return $this->container->get('orocrm_contact.contact.manager.api');
    }

    /**
     * @return FormInterface
     */
    public function getForm()
    {
        return $this->container->get('orocrm_contact.form.contact.api');
    }

    /**
     * @return ApiFormHandler
     */
    public function getFormHandler()
    {
        return $this->container->get('orocrm_contact.form.handler.contact.api');
    }
}
