<?php

namespace OroCRM\Bundle\CaseBundle\Model;

use Doctrine\ORM\EntityManager;

use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

use OroCRM\Bundle\CaseBundle\Entity\CaseComment;
use OroCRM\Bundle\CaseBundle\Entity\CaseEntity;
use OroCRM\Bundle\CaseBundle\Entity\CasePriority;
use OroCRM\Bundle\CaseBundle\Entity\CaseSource;

class CaseEntityManager
{
    /**
     * @var EntityManager
     */
    protected $entityManager;
    /**
     * @var AclHelper
     */
    protected $aclHelper;

    /**
     * @param EntityManager $entityManager
     * @param AclHelper $aclHelper
     */
    public function __construct(
        EntityManager $entityManager,
        AclHelper $aclHelper
    ) {
        $this->entityManager = $entityManager;
        $this->aclHelper = $aclHelper;
    }

    /**
     * @return CaseEntity
     */
    public function createCase()
    {
        return $this->createCaseObject()
            ->setPriority($this->getDefaultCasePriority())
            ->setSource($this->getDefaultCaseSource());
    }

    /**
     * @return CaseEntity
     */
    protected function createCaseObject()
    {
        return new CaseEntity();
    }

    /**
     * @return CasePriority|null
     */
    protected function getDefaultCasePriority()
    {
        return $this->entityManager
            ->getRepository('OroCRMCaseBundle:CasePriority')
            ->findOneByName(CasePriority::PRIORITY_NORMAL);
    }

    /**
     * @return CaseSource|null
     */
    protected function getDefaultCaseSource()
    {
        return $this->entityManager
            ->getRepository('OroCRMCaseBundle:CaseSource')
            ->findOneByName(CaseSource::SOURCE_OTHER);
    }

    /**
     * @param CaseEntity $case
     * @return CaseComment
     */
    public function createComment(CaseEntity $case = null)
    {
        $comment = $this->createCommentObject();

        if ($case) {
            $case->addComment($comment);
        }

        return $comment;
    }

    /**
     * @return CaseComment
     */
    protected function createCommentObject()
    {
        return new CaseComment();
    }

    /**
     * Get ordered list of case comments
     *
     * @param CaseEntity $case
     * @param string $order
     * @return CaseComment[]
     */
    public function getCaseComments(CaseEntity $case, $order = 'DESC')
    {
        $order = (strtoupper($order) == 'ASC') ? $order : 'DESC';
        $repository = $this->entityManager->getRepository('OroCRMCaseBundle:CaseComment');
        $queryBuilder = $repository->createQueryBuilder('comment')
            ->where('comment.case = :case')
            ->orderBy('comment.createdAt', $order)
            ->setParameter('case', $case);

        $query = $this->aclHelper->apply($queryBuilder);

        return $query->getResult();
    }
}
