<?php

namespace OroCRM\Bundle\CaseBundle\Controller\Api\Soap;

use BeSimple\SoapBundle\ServiceDefinition\Annotation as Soap;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SoapBundle\Controller\Api\Soap\SoapController;

class CaseController extends SoapController
{
    /**
     * @Soap\Method("getCases")
     * @Soap\Param("page", phpType="int")
     * @Soap\Param("limit", phpType="int")
     * @Soap\Param("order", phpType="string")
     * @Soap\Result(phpType="OroCRM\Bundle\CaseBundle\Entity\CaseEntitySoap[]")
     * @AclAncestor("orocrm_case_view")
     */
    public function cgetAction($page = 1, $limit = 10, $order = 'DESC')
    {
        $order = (strtoupper($order) == 'ASC') ? $order : 'DESC';
        return $this->handleGetListRequest($page, $limit, [], array('reportedAt' => $order));
    }

    /**
     * @Soap\Method("getCase")
     * @Soap\Param("id", phpType="int")
     * @Soap\Result(phpType="OroCRM\Bundle\CaseBundle\Entity\CaseEntitySoap")
     * @AclAncestor("orocrm_case_view")
     */
    public function getAction($id)
    {
        return $this->handleGetRequest($id);
    }

    /**
     * @Soap\Method("createCase")
     * @Soap\Param("case", phpType="OroCRM\Bundle\CaseBundle\Entity\CaseEntitySoap")
     * @Soap\Result(phpType="int")
     * @AclAncestor("orocrm_case_create")
     */
    public function createAction()
    {
        return $this->handleCreateRequest();
    }

    /**
     * @Soap\Method("updateCase")
     * @Soap\Param("id", phpType="int")
     * @Soap\Param("case", phpType="OroCRM\Bundle\CaseBundle\Entity\CaseEntitySoap")
     * @Soap\Result(phpType="boolean")
     * @AclAncestor("orocrm_case_update")
     */
    public function updateAction($id)
    {
        return $this->handleUpdateRequest($id);
    }

    /**
     * @Soap\Method("deleteCase")
     * @Soap\Param("id", phpType="int")
     * @Soap\Result(phpType="boolean")
     * @AclAncestor("orocrm_case_delete")
     */
    public function deleteAction($id)
    {
        return $this->handleDeleteRequest($id);
    }

    /**
     * {@inheritdoc}
     */
    public function getManager()
    {
        return $this->container->get('orocrm_case.manager.api');
    }

    /**
     * {@inheritdoc}
     */
    public function getForm()
    {
        return $this->container->get('orocrm_case.form.entity.api');
    }

    /**
     * {@inheritdoc}
     */
    public function getFormHandler()
    {
        return $this->container->get('orocrm_case.form.handler.entity.api');
    }

    /**
     * {@inheritDoc}
     */
    protected function fixFormData(array &$data, $entity)
    {
        parent::fixFormData($data, $entity);

        unset($data['id']);
        unset($data['createdAt']);
        unset($data['updatedAt']);

        return true;
    }
}
