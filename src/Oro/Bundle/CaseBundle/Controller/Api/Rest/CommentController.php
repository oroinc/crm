<?php

namespace Oro\Bundle\CaseBundle\Controller\Api\Rest;

use FOS\RestBundle\Controller\Annotations\QueryParam;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Oro\Bundle\CaseBundle\Entity\CaseEntity;
use Oro\Bundle\SecurityBundle\Attribute\AclAncestor;
use Oro\Bundle\SoapBundle\Controller\Api\Rest\RestController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * REST API CRUD controller for Comment entity.
 */
class CommentController extends RestController
{
    /**
     * REST GET list
     *
     * @ApiDoc(
     *     description="Get list of case comments",
     *     resource=true
     * )
     * @param Request $request
     * @param CaseEntity $case
     * @return Response
     */
    #[QueryParam(
        name: 'order',
        requirements: 'ASC|DESC',
        description: 'Order of comments by created at field.',
        nullable: true
    )]
    #[AclAncestor('oro_case_comment_view')]
    public function cgetAction(Request $request, CaseEntity $case)
    {
        $comments = $this->container->get('oro_case.manager')
            ->getCaseComments(
                $case,
                $request->get('order', 'DESC')
            );

        return $this->handleView(
            $this->view($this->getPreparedItems($comments), Response::HTTP_OK)
        );
    }

    /**
     * REST GET item
     *
     * @param int $id
     *
     * @ApiDoc(
     *     description="Get CaseComment item",
     *     resource=true
     * )
     * @return Response
     */
    #[AclAncestor('oro_case_comment_view')]
    public function getAction(int $id)
    {
        return $this->handleGetRequest($id);
    }

    /**
     * REST PUT
     *
     * @param int $id CaseComment item id
     *
     * @ApiDoc(
     *     description="Update CaseComment",
     *     resource=true
     * )
     * @return Response
     */
    #[AclAncestor('oro_case_comment_update')]
    public function putAction(int $id)
    {
        return $this->handleUpdateRequest($id);
    }

    /**
     * Create new case
     * @ApiDoc(
     *     description="Create new CaseComment",
     *     resource=true
     * )
     */
    #[AclAncestor('oro_case_comment_create')]
    public function postAction(CaseEntity $case)
    {
        return $this->handleCreateRequest($case);
    }

    /**
     * REST DELETE
     *
     * @param int $id
     *
     * @ApiDoc(
     *     description="Delete CaseComment",
     *     resource=true
     * )
     * @return Response
     */
    #[AclAncestor('oro_case_comment_delete')]
    public function deleteAction(int $id)
    {
        return $this->handleDeleteRequest($id);
    }

    #[\Override]
    public function getManager()
    {
        return $this->container->get('oro_case.manager.comment.api');
    }

    #[\Override]
    public function getForm()
    {
        return $this->container->get('oro_case.form.comment.api');
    }

    #[\Override]
    public function getFormHandler()
    {
        return $this->container->get('oro_case.form.handler.comment.api');
    }

    #[\Override]
    protected function transformEntityField($field, &$value)
    {
        switch ($field) {
            case 'case':
            case 'owner':
            case 'contact':
                if ($value) {
                    /** @var CaseEntity $value */
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
