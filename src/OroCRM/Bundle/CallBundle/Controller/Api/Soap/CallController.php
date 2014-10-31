<?php

namespace OroCRM\Bundle\CallBundle\Controller\Api\Soap;

use Symfony\Component\Form\FormInterface;
use BeSimple\SoapBundle\ServiceDefinition\Annotation as Soap;

use Oro\Bundle\SoapBundle\Entity\Manager\ApiEntityManager;
use Oro\Bundle\SoapBundle\Controller\Api\Soap\SoapController;
use Oro\Bundle\SoapBundle\Form\Handler\ApiFormHandler;

class CallController extends SoapController
{
    /**
     * @Soap\Method("getCalls")
     * @Soap\Param("page", phpType="int")
     * @Soap\Param("limit", phpType="int")
     * @Soap\Result(phpType = "OroCRM\Bundle\CallBundle\Entity\Call[]")
     */
    public function cgetAction($page = 1, $limit = 10)
    {
        return $this->handleGetListRequest($page, $limit);
    }

    /**
     * @Soap\Method("getCall")
     * @Soap\Param("id", phpType = "int")
     * @Soap\Result(phpType = "OroCRM\Bundle\CallBundle\Entity\Call")
     */
    public function getAction($id)
    {
        return $this->handleGetRequest($id);
    }

    /**
     * @Soap\Method("createCall")
     * @Soap\Param("call", phpType = "OroCRM\Bundle\CallBundle\Entity\Call")
     * @Soap\Result(phpType = "int")
     */
    public function createAction($call)
    {
        return $this->handleCreateRequest();
    }

    /**
     * @Soap\Method("updateCall")
     * @Soap\Param("id", phpType = "int")
     * @Soap\Param("call", phpType = "OroCRM\Bundle\CallBundle\Entity\Call")
     * @Soap\Result(phpType = "boolean")
     */
    public function updateAction($id, $call)
    {
        return $this->handleUpdateRequest($id);
    }

    /**
     * @Soap\Method("deleteCall")
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
        return $this->container->get('orocrm_call.call.manager.api');
    }

    /**
     * @return FormInterface
     */
    public function getForm()
    {
        return $this->container->get('orocrm_call.call.form.api');
    }

    /**
     * @return ApiFormHandler
     */
    public function getFormHandler()
    {
        return $this->container->get('orocrm_call.call.form.handler.api');
    }
}
