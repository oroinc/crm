<?php

namespace OroCRM\Bundle\AccountBundle\Controller\Api\Soap;

use Symfony\Component\Form\FormInterface;
use BeSimple\SoapBundle\ServiceDefinition\Annotation as Soap;

use Oro\Bundle\SoapBundle\Entity\Manager\ApiEntityManager;
use Oro\Bundle\SoapBundle\Controller\Api\Soap\SoapController;
use Oro\Bundle\SoapBundle\Form\Handler\ApiFormHandler;

class AccountController extends SoapController
{
    /**
     * @Soap\Method("getAccounts")
     * @Soap\Param("page", phpType="int")
     * @Soap\Param("limit", phpType="int")
     * @Soap\Result(phpType = "OroCRM\Bundle\AccountBundle\Entity\Account[]")
     */
    public function cgetAction($page = 1, $limit = 10)
    {
        return $this->handleGetListRequest($page, $limit);
    }

    /**
     * @Soap\Method("getAccount")
     * @Soap\Param("id", phpType = "int")
     * @Soap\Result(phpType = "OroCRM\Bundle\AccountBundle\Entity\Account")
     */
    public function getAction($id)
    {
        return $this->handleGetRequest($id);
    }

    /**
     * @Soap\Method("createAccount")
     * @Soap\Param("account", phpType = "OroCRM\Bundle\AccountBundle\Entity\Account")
     * @Soap\Result(phpType = "int")
     */
    public function createAction($account)
    {
        return $this->handleCreateRequest();
    }

    /**
     * @Soap\Method("updateAccount")
     * @Soap\Param("id", phpType = "int")
     * @Soap\Param("account", phpType = "OroCRM\Bundle\AccountBundle\Entity\Account")
     * @Soap\Result(phpType = "boolean")
     */
    public function updateAction($id, $account)
    {
        return $this->handleUpdateRequest($id);
    }

    /**
     * @Soap\Method("deleteAccount")
     * @Soap\Param("id", phpType = "int")
     * @Soap\Result(phpType = "boolean")
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
        return $this->container->get('orocrm_account.account.manager.api');
    }

    /**
     * @return FormInterface
     */
    public function getForm()
    {
        return $this->container->get('orocrm_account.form.account.api');
    }

    /**
     * @return ApiFormHandler
     */
    public function getFormHandler()
    {
        return $this->container->get('orocrm_account.form.handler.account.api');
    }
}
