<?php

namespace OroCRM\Bundle\CaseBundle\Controller\Api\Soap;

use BeSimple\SoapBundle\ServiceDefinition\Annotation as Soap;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SoapBundle\Controller\Api\Soap\SoapController;

use OroCRM\Bundle\CaseBundle\Entity\CaseEntity;
use OroCRM\Bundle\CaseBundle\Model\CaseApiEntityManager;
use OroCRM\Bundle\CaseBundle\Model\CommentApiEntityManager;

class CommentController extends SoapController
{
    /**
     * @Soap\Method("getCaseComments")
     * @Soap\Param("caseId", phpType="int")
     * @Soap\Param("order", phpType="string")
     * @Soap\Result(phpType="OroCRM\Bundle\CaseBundle\Entity\CaseCommentSoap[]")
     * @AclAncestor("orocrm_case_comment_view")
     */
    public function cgetAction($caseId, $order = 'DESC')
    {
        $order = (strtoupper($order) == 'ASC') ? $order : 'DESC';
        $comments = $this->getManager()->getCaseComments($this->getCase($caseId), $order);

        return $this->transformToSoapEntities($comments);
    }

    /**
     * @Soap\Method("getCaseComment")
     * @Soap\Param("id", phpType="int")
     * @Soap\Result(phpType="OroCRM\Bundle\CaseBundle\Entity\CaseCommentSoap")
     * @AclAncestor("orocrm_case_comment_view")
     */
    public function getAction($id)
    {
        return $this->handleGetRequest($id);
    }

    /**
     * @Soap\Method("createCaseComment")
     * @Soap\Param("caseId", phpType="int")
     * @Soap\Param("comment", phpType="OroCRM\Bundle\CaseBundle\Entity\CaseCommentSoap")
     * @Soap\Result(phpType="int")
     * @AclAncestor("orocrm_case_comment_create")
     */
    public function createAction($caseId)
    {
        $case = $this->getCase($caseId);
        return $this->handleCreateRequest($case);
    }

    /**
     * @Soap\Method("updateCaseComment")
     * @Soap\Param("id", phpType="int")
     * @Soap\Param("comment", phpType="OroCRM\Bundle\CaseBundle\Entity\CaseCommentSoap")
     * @Soap\Result(phpType="boolean")
     * @AclAncestor("orocrm_case_comment_update")
     */
    public function updateAction($id)
    {
        return $this->handleUpdateRequest($id);
    }

    /**
     * @Soap\Method("deleteCaseComment")
     * @Soap\Param("id", phpType="int")
     * @Soap\Result(phpType="boolean")
     * @AclAncestor("orocrm_case_comment_delete")
     */
    public function deleteAction($id)
    {
        return $this->handleDeleteRequest($id);
    }

    /**
     * @return CommentApiEntityManager
     */
    public function getManager()
    {
        return $this->container->get('orocrm_case.manager.comment.api');
    }

    /**
     * @return CaseApiEntityManager
     */
    protected function getCaseManager()
    {
        return $this->container->get('orocrm_case.manager.api');
    }

    /**
     * {@inheritdoc}
     */
    public function getForm()
    {
        return $this->container->get('orocrm_case.form.comment.api');
    }

    /**
     * {@inheritdoc}
     */
    public function getFormHandler()
    {
        return $this->container->get('orocrm_case.form.handler.comment.api');
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

    /**
     * @param int $id
     * @return CaseEntity
     * @throws \SoapFault
     */
    protected function getCase($id)
    {
        $case = $this->getCaseManager()->find($id);

        if (!$case) {
            throw new \SoapFault('NOT_FOUND', sprintf('Record with ID "%s" can not be found', $case));
        }

        return $case;
    }
}
