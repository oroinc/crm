<?php

namespace Oro\Bundle\CaseBundle\Controller\Api\Rest;

use FOS\RestBundle\Controller\Annotations\QueryParam;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Oro\Bundle\CaseBundle\Entity\CaseEntity;
use Oro\Bundle\CaseBundle\Entity\CaseSource;
use Oro\Bundle\CaseBundle\Entity\CaseStatus;
use Oro\Bundle\SecurityBundle\Attribute\AclAncestor;
use Oro\Bundle\SoapBundle\Controller\Api\Rest\RestController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * REST API CRUD controller for Case entity.
 *
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 */
class CaseController extends RestController
{
    /**
     * REST GET list
     *
     * @ApiDoc(
     *     description="Get all CaseEntity items",
     *     resource=true
     * )
     * @param Request $request
     * @return Response
     */
    #[QueryParam(
        name: 'page',
        requirements: '\d+',
        description: 'Page number, starting from 1. Defaults to 1.',
        nullable: true
    )]
    #[QueryParam(
        name: 'limit',
        requirements: '\d+',
        description: 'Number of items per page. defaults to 10.',
        nullable: true
    )]
    #[AclAncestor('oro_case_view')]
    public function cgetAction(Request $request)
    {
        $page  = (int)$request->get('page', 1);
        $limit = (int)$request->get('limit', self::ITEMS_PER_PAGE);

        return $this->handleGetListRequest($page, $limit);
    }

    /**
     * REST GET item
     *
     * @param int $id
     *
     * @ApiDoc(
     *     description="Get CaseEntity item",
     *     resource=true
     * )
     * @return Response
     */
    #[AclAncestor('oro_case_view')]
    public function getAction(int $id)
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
     * @return Response
     */
    #[AclAncestor('oro_case_update')]
    public function putAction(int $id)
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
     */
    #[AclAncestor('oro_case_create')]
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
     * @return Response
     */
    #[AclAncestor('oro_case_delete')]
    public function deleteAction(int $id)
    {
        return $this->handleDeleteRequest($id);
    }

    #[\Override]
    public function getManager()
    {
        return $this->container->get('oro_case.manager.api');
    }

    #[\Override]
    public function getForm()
    {
        return $this->container->get('oro_case.form.entity.api');
    }

    #[\Override]
    public function getFormHandler()
    {
        return $this->container->get('oro_case.form.handler.entity.api');
    }

    #[\Override]
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

    #[\Override]
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
