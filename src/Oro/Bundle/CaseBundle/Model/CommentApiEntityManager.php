<?php

namespace Oro\Bundle\CaseBundle\Model;

use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\CaseBundle\Entity\CaseComment;
use Oro\Bundle\CaseBundle\Entity\CaseEntity;
use Oro\Bundle\SoapBundle\Entity\Manager\ApiEntityManager;

class CommentApiEntityManager extends ApiEntityManager
{
    /**
     * @var CaseEntityManager
     */
    protected $caseManager;

    /**
     * Constructor
     *
     * @param string $class Entity name
     * @param ObjectManager $om Object manager
     * @param CaseEntityManager $caseManager
     */
    public function __construct($class, ObjectManager $om, CaseEntityManager $caseManager)
    {
        $this->caseManager = $caseManager;
        parent::__construct($class, $om);
    }

    /**
     * @param CaseEntity $case
     * @return CaseComment
     */
    public function createEntity(CaseEntity $case = null)
    {
        return $this->caseManager->createComment($case);
    }

    /**
     * @param CaseEntity $case
     * @param string $order
     * @return CaseComment[]
     */
    public function getCaseComments(CaseEntity $case, $order = 'DESC')
    {
        return $this->caseManager->getCaseComments($case, $order);
    }
}
