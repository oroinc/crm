<?php

namespace Oro\Bundle\CaseBundle\Controller\Api\Rest;

use FOS\RestBundle\Controller\Annotations\QueryParam;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Oro\Bundle\CaseBundle\Entity\CaseEntity;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
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
     * @QueryParam(
     *     name="order",
     *     requirements="ASC|DESC",
     *     nullable=true,
     *     description="Order of comments by created at field."
     * )
     * @ApiDoc(
     *     description="Get list of case comments",
     *     resource=true
     * )
     * @AclAncestor("oro_case_comment_view")
     * @param Request $request
     * @param CaseEntity $case
     * @return Response
     */
    public function cgetAction(Request $request, CaseEntity $case)
    {
        $comments = $this->get('oro_case.manager')
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
     * @AclAncestor("oro_case_comment_view")
     * @return Response
     */
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
     * @AclAncestor("oro_case_comment_update")
     * @return Response
     */
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
     * @AclAncestor("oro_case_comment_create")
     */
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
     * @AclAncestor("oro_case_comment_delete")
     * @return Response
     */
    public function deleteAction(int $id)
    {
        return $this->handleDeleteRequest($id);
    }

    /**
     * {@inheritdoc}
     */
    public function getManager()
    {
        return $this->get('oro_case.manager.comment.api');
    }

    /**
     * {@inheritdoc}
     */
    public function getForm()
    {
        return $this->get('oro_case.form.comment.api');
    }

    /**
     * {@inheritdoc}
     */
    public function getFormHandler()
    {
        return $this->get('oro_case.form.handler.comment.api');
    }

    /**
     * {@inheritdoc}
     */
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
