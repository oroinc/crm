<?php

namespace OroCRM\Bundle\CaseBundle\Controller\Api\Rest;

use Symfony\Component\HttpFoundation\Response;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Routing\ClassResourceInterface;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SoapBundle\Controller\Api\Rest\RestController;

use OroCRM\Bundle\CaseBundle\Entity\CaseSource;
use OroCRM\Bundle\CaseBundle\Entity\CaseStatus;
use OroCRM\Bundle\CaseBundle\Entity\CaseEntity;

/**
 * @Rest\RouteResource("case")
 * @Rest\NamePrefix("orocrm_case_api_")
 */
class CaseController extends RestController implements ClassResourceInterface
{
    /**
     * REST GET list
     *
     * @Rest\QueryParam(
     *     name="page",
     *     requirements="\d+",
     *     nullable=true,
     *     description="Page number, starting from 1. Defaults to 1."
     * )
     * @Rest\QueryParam(
     *     name="limit",
     *     requirements="\d+",
     *     nullable=true,
     *     description="Number of items per page. defaults to 10."
     * )
     * @ApiDoc(
     *     description="Get all CaseEntity items",
     *     resource=true
     * )
     * @AclAncestor("orocrm_case_view")
     * @return Response
     */
    public function cgetAction()
    {
        $page  = (int)$this->getRequest()->get('page', 1);
        $limit = (int)$this->getRequest()->get('limit', self::ITEMS_PER_PAGE);

        return $this->handleGetListRequest($page, $limit);
    }

    /**
     * REST GET item
     *
     * @param string $id
     *
     * @ApiDoc(
     *     description="Get CaseEntity item",
     *     resource=true
     * )
     * @AclAncestor("orocrm_case_view")
     * @return Response
     */
    public function getAction($id)
    {
        return $this->handleGetRequest($id);
    }

    /**
     * REST PUT
     *
     * @param int $id CaseEntity item id
     *
     * @ApiDoc(
     *     description="Update CaseEntity",
     *     resource=true
     * )
     * @AclAncestor("orocrm_case_update")
     * @return Response
     */
    public function putAction($id)
    {
        return $this->handleUpdateRequest($id);
    }

    /**
     * Create new case
     *
     * @ApiDoc(
     *     description="Create new CaseEntity",
     *     resource=true
     * )
     * @AclAncestor("orocrm_case_create")
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
     *     description="Delete CaseEntity",
     *     resource=true
     * )
     * @AclAncestor("orocrm_case_delete")
     * @return Response
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
        return $this->get('orocrm_case.manager.api');
    }

    /**
     * {@inheritdoc}
     */
    public function getForm()
    {
        return $this->get('orocrm_case.form.entity.api');
    }

    /**
     * {@inheritdoc}
     */
    public function getFormHandler()
    {
        return $this->get('orocrm_case.form.handler.entity.api');
    }

    /**
     * {@inheritdoc}
     */
    protected function transformEntityField($field, &$value)
    {
        switch ($field) {
            case 'source':
            case 'priority':
            case 'status':
                if ($value) {
                    /** @var CaseSource|CaseStatus $value */
                    $value = $value->getName();
                }
                break;
            case 'owner':
            case 'assignedTo':
            case 'relatedContact':
            case 'relatedAccount':
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
        /** @var CaseEntity $entity */
        parent::fixFormData($data, $entity);

        unset($data['id']);
        unset($data['createdAt']);
        unset($data['updatedAt']);

        return true;
    }
}
