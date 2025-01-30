<?php

namespace Oro\Bundle\CaseBundle\Model;

use Doctrine\ORM\EntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\CaseBundle\Entity\CaseComment;
use Oro\Bundle\CaseBundle\Entity\CaseEntity;
use Oro\Bundle\CaseBundle\Entity\CasePriority;
use Oro\Bundle\CaseBundle\Entity\CaseSource;
use Oro\Bundle\CaseBundle\Entity\CaseStatus;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

/**
 * Simplifies work with CaseEntity and CaseComment entities
 */
class CaseEntityManager
{
    /** @var ManagerRegistry */
    protected $registry;

    /** @var AclHelper */
    protected $aclHelper;

    public function __construct(ManagerRegistry $registry, AclHelper $aclHelper)
    {
        $this->registry  = $registry;
        $this->aclHelper = $aclHelper;
    }

    /**
     * @return CaseEntity
     */
    public function createCase()
    {
        return $this->createCaseObject()
            ->setStatus($this->getDefaultCaseStatus())
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
     * @return CaseStatus|null
     */
    protected function getDefaultCaseStatus()
    {
        return $this->registry->getManager()->find(CaseStatus::class, CaseStatus::STATUS_OPEN);
    }

    /**
     * @return CasePriority|null
     */
    protected function getDefaultCasePriority()
    {
        return $this->registry->getManager()->find(CasePriority::class, CasePriority::PRIORITY_NORMAL);
    }

    /**
     * @return CaseSource|null
     */
    protected function getDefaultCaseSource()
    {
        return $this->registry->getManager()->find(CaseSource::class, CaseSource::SOURCE_OTHER);
    }

    /**
     * @param CaseEntity|null $case
     *
     * @return CaseComment
     */
    public function createComment(?CaseEntity $case = null)
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
     * @param string     $order
     *
     * @return CaseComment[]
     */
    public function getCaseComments(CaseEntity $case, $order = 'DESC')
    {
        $order = (strtoupper($order) === 'ASC') ? 'ASC' : 'DESC';
        /** @var EntityRepository $repository */
        $repository   = $this->registry->getRepository(CaseComment::class);
        $queryBuilder = $repository->createQueryBuilder('comment')
            ->where('comment.case = :case')
            ->orderBy('comment.createdAt', $order)
            ->setParameter('case', $case);

        $query = $this->aclHelper->apply($queryBuilder);

        return $query->getResult();
    }
}
