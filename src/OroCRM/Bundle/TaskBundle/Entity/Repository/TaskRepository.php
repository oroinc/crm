<?php

namespace OroCRM\Bundle\TaskBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

class TaskRepository extends EntityRepository
{
    const CLOSED_STATE = 'closed';

    public function getTaskAssignedTo($userId, $limit)
    {
        $queryBuilder = $this->createQueryBuilder('task');
        return $queryBuilder->where('task.owner = :assignedTo AND step.name != :step')
            ->innerJoin('task.workflowStep', 'step')
            ->orderBy('task.dueDate', 'ASC')
            ->addOrderBy('task.workflowStep', 'ASC')
            ->setFirstResult(0)
            ->setMaxResults($limit)
            ->setParameters(array('assignedTo' => $userId, 'step' => TaskRepository::CLOSED_STATE))
            ->getQuery()
            ->execute();
    }
}
