<?php

namespace OroCRM\Bundle\CaseBundle\Controller\Api\Rest;

use Symfony\Component\HttpFoundation\Response;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use FOS\RestBundle\Util\Codes;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Routing\ClassResourceInterface;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SoapBundle\Controller\Api\Rest\RestController;

use OroCRM\Bundle\CaseBundle\Entity\CaseEntity;

/**
 * @Rest\RouteResource("case/comment")
 * @Rest\NamePrefix("orocrm_case_api_")
 */
class CommentController extends RestController implements ClassResourceInterface
{
    /**
     * REST GET list
     *
     * @Rest\Get(
     *      "/case/{id}/comments",
     *      requirements={"id"="\d+"}
     * )
     * @Rest\QueryParam(
     *     name="order",
     *     requirements="ASC|DESC",
     *     nullable=true,
     *     description="Order of comments by created at field."
     * )
     * @ApiDoc(
     *     description="Get list of case comments",
     *     resource=true
     * )
     * @AclAncestor("orocrm_case_comment_view")
     */
    public function cgetAction(CaseEntity $case)
    {
        $comments = $this->get('orocrm_case.manager')
            ->getCaseComments(
                $case,
                $this->getRequest()->get('order', 'DESC')
            );

        return $this->handleView(
            $this->view($this->getPreparedItems($comments), Codes::HTTP_OK)
        );
    }

    /**
     * REST GET item
     *
     * @param string $id
     *
     * @ApiDoc(
     *     description="Get CaseComment item",
     *     resource=true
     * )
     * @AclAncestor("orocrm_case_comment_view")
     * @return Response
     */
    public function getAction($id)
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
     * @AclAncestor("orocrm_case_comment_update")
     * @return Response
     */
    public function putAction($id)
    {
        return $this->handleUpdateRequest($id);
    }

    /**
     * Create new case

     * @Rest\Post(
     *      "/case/{id}/comment",
     *      requirements={"id"="\d+"}
     * )
     * @ApiDoc(
     *     description="Create new CaseComment",
     *     resource=true
     * )
     * @AclAncestor("orocrm_case_comment_create")
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
     * @AclAncestor("orocrm_case_comment_delete")
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
        return $this->get('orocrm_case.manager.comment.api');
    }

    /**
     * {@inheritdoc}
     */
    public function getForm()
    {
        return $this->get('orocrm_case.form.comment.api');
    }

    /**
     * {@inheritdoc}
     */
    public function getFormHandler()
    {
        return $this->get('orocrm_case.form.handler.comment.api');
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
