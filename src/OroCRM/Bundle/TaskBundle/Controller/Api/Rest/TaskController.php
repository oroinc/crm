<?php

namespace OroCRM\Bundle\TaskBundle\Controller\Api\Rest;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Response;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Routing\ClassResourceInterface;

use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SoapBundle\Entity\Manager\ApiEntityManager;
use Oro\Bundle\SoapBundle\Form\Handler\ApiFormHandler;
use Oro\Bundle\SoapBundle\Controller\Api\Rest\RestController;
use Oro\Bundle\SoapBundle\Request\Parameters\Filter\HttpDateTimeParameterFilter;
use Oro\Bundle\SoapBundle\Request\Parameters\Filter\IdentifierToReferenceFilter;

/**
 * @RouteResource("task")
 * @NamePrefix("orocrm_api_")
 */
class TaskController extends RestController implements ClassResourceInterface
{
    /**
     * REST GET list
     *
     * @QueryParam(
     *      name="page",
     *      requirements="\d+",
     *      nullable=true,
     *      description="Page number, starting from 1. Defaults to 1."
     * )
     * @QueryParam(
     *      name="limit",
     *      requirements="\d+",
     *      nullable=true,
     *      description="Number of items per page. defaults to 10."
     * )
     * @QueryParam(
     *     name="createdAt",
     *     requirements="\d{4}(-\d{2}(-\d{2}([T ]\d{2}:\d{2}(:\d{2}(\.\d+)?)?(Z|([-+]\d{2}(:?\d{2})?))?)?)?)?",
     *     nullable=true,
     *     description="Date in RFC 3339 format. For example: 2009-11-05T13:15:30Z, 2008-07-01T22:35:17+08:00"
     * )
     * @QueryParam(
     *     name="updatedAt",
     *     requirements="\d{4}(-\d{2}(-\d{2}([T ]\d{2}:\d{2}(:\d{2}(\.\d+)?)?(Z|([-+]\d{2}(:?\d{2})?))?)?)?)?",
     *     nullable=true,
     *     description="Date in RFC 3339 format. For example: 2009-11-05T13:15:30Z, 2008-07-01T22:35:17+08:00"
     * )
     * @QueryParam(
     *     name="ownerId",
     *     requirements="\d+",
     *     nullable=true,
     *     description="Id of owner assignee"
     * )
     * @QueryParam(
     *     name="ownerUsername",
     *     requirements=".+",
     *     nullable=true,
     *     description="Username of owner assignee"
     * )
     * @ApiDoc(
     *      description="Get all task items",
     *      resource=true
     * )
     * @AclAncestor("orocrm_task_view")
     * @return Response
     */
    public function cgetAction()
    {
        $page  = (int)$this->getRequest()->get('page', 1);
        $limit = (int)$this->getRequest()->get('limit', self::ITEMS_PER_PAGE);

        $dateParamFilter  = new HttpDateTimeParameterFilter();
        $filterParameters = [
            'createdAt'     => $dateParamFilter,
            'updatedAt'     => $dateParamFilter,
            'ownerId'       => new IdentifierToReferenceFilter($this->getDoctrine(), 'OroUserBundle:User'),
            'ownerUsername' => new IdentifierToReferenceFilter($this->getDoctrine(), 'OroUserBundle:User', 'username'),
        ];
        $map              = array_fill_keys(['ownerId', 'ownerUsername'], 'owner');

        $criteria = $this->getFilterCriteria($this->getSupportedQueryParameters('cgetAction'), $filterParameters, $map);

        return $this->handleGetListRequest($page, $limit, $criteria);
    }

    /**
     * REST GET item
     *
     * @param string $id
     *
     * @ApiDoc(
     *      description="Get task item",
     *      resource=true
     * )
     * @AclAncestor("orocrm_task_view")
     * @return Response
     */
    public function getAction($id)
    {
        return $this->handleGetRequest($id);
    }

    /**
     * REST PUT
     *
     * @param int $id Task item id
     *
     * @ApiDoc(
     *      description="Update task",
     *      resource=true
     * )
     * @AclAncestor("orocrm_task_update")
     * @return Response
     */
    public function putAction($id)
    {
        return $this->handleUpdateRequest($id);
    }

    /**
     * Create new task
     *
     * @ApiDoc(
     *      description="Create new task",
     *      resource=true
     * )
     * @AclAncestor("orocrm_task_create")
     */
    public function postAction()
    {
        return $this->handleCreateRequest();
    }

    /**
     * REST DELETE
     *
     * @param int $id
     *
     * @ApiDoc(
     *      description="Delete Task",
     *      resource=true
     * )
     * @Acl(
     *      id="orocrm_task_delete",
     *      type="entity",
     *      permission="DELETE",
     *      class="OroCRMTaskBundle:Task"
     * )
     * @return Response
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
        return $this->get('orocrm_task.manager.api');
    }

    /**
     * @return FormInterface
     */
    public function getForm()
    {
        return $this->get('orocrm_task.form.api');
    }

    /**
     * @return ApiFormHandler
     */
    public function getFormHandler()
    {
        return $this->get('orocrm_task.form.handler.task_api');
    }

    /**
     * {@inheritdoc}
     */
    protected function transformEntityField($field, &$value)
    {
        switch ($field) {
            case 'taskPriority':
                if ($value) {
                    $value = $value->getName();
                }
                break;
            case 'owner':
            case 'workflowItem':
            case 'workflowStep':
                if ($value) {
                    $value = $value->getId();
                }
                break;
            default:
                parent::transformEntityField($field, $value);
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function fixFormData(array &$data, $entity)
    {
        parent::fixFormData($data, $entity);

        unset($data['id']);
        unset($data['updatedAt']);

        return true;
    }
}
