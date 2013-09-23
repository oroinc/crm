<?php

namespace OroCRM\Bundle\ContactBundle\Controller\Api\Soap;

use Symfony\Component\Form\FormInterface;
use BeSimple\SoapBundle\ServiceDefinition\Annotation as Soap;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SoapBundle\Controller\Api\Soap\SoapController;
use Oro\Bundle\SoapBundle\Entity\Manager\ApiEntityManager;
use Oro\Bundle\SoapBundle\Form\Handler\ApiFormHandler;

class ContactGroupController extends SoapController
{
    /**
     * @Soap\Method("getContactGroups")
     * @Soap\Param("page", phpType="int")
     * @Soap\Param("limit", phpType="int")
     * @Soap\Result(phpType = "OroCRM\Bundle\ContactBundle\Entity\Group[]")
     * @AclAncestor("orocrm_contact_group_view")
     */
    public function cgetAction($page = 1, $limit = 10)
    {
        return $this->handleGetListRequest($page, $limit);
    }

    /**
     * @Soap\Method("getContactGroup")
     * @Soap\Param("id", phpType = "int")
     * @Soap\Result(phpType = "OroCRM\Bundle\ContactBundle\Entity\Group")
     * @AclAncestor("orocrm_contact_group_view")
     */
    public function getAction($id)
    {
        return $this->handleGetRequest($id);
    }

    /**
     * @Soap\Method("createContactGroup")
     * @Soap\Param("contact_group", phpType = "OroCRM\Bundle\ContactBundle\Entity\Group")
     * @Soap\Result(phpType = "boolean")
     * @AclAncestor("orocrm_contact_group_create")
     */
    public function createAction($contact_group)
    {
        return $this->handleCreateRequest();
    }

    /**
     * @Soap\Method("updateContactGroup")
     * @Soap\Param("id", phpType = "int")
     * @Soap\Param("contact_group", phpType = "OroCRM\Bundle\ContactBundle\Entity\Group")
     * @Soap\Result(phpType = "boolean")
     * @AclAncestor("orocrm_contact_group_update")
     */
    public function updateAction($id, $contact_group)
    {
        return $this->handleUpdateRequest($id);
    }

    /**
     * @Soap\Method("deleteContactGroup")
     * @Soap\Param("id", phpType = "int")
     * @Soap\Result(phpType = "boolean")
     * @AclAncestor("orocrm_contact_group_delete")
     */
    public function deleteAction($id)
    {
        return $this->handleDeleteRequest($id);
    }

    /**
     * Get entity Manager
     *
     * @return ApiEntityManager
     */
    public function getManager()
    {
        return $this->container->get('orocrm_contact.group.manager.api');
    }

    /**
     * @return FormInterface
     */
    public function getForm()
    {
        return $this->container->get('orocrm_contact.form.group.api');
    }

    /**
     * @return ApiFormHandler
     */
    public function getFormHandler()
    {
        return $this->container->get('orocrm_contact.form.handler.group.api');
    }
}
