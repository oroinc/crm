<?php

namespace OroCRM\Bundle\TaskBundle\Entity\Repository;

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

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

    /**
     * Returns a query builder which can be used to get a list of tasks filtered by start and end dates
     *
     * @param int $userId
     * @param \DateTime $startDate
     * @param \DateTime $endDate
     * @param array|Criteria $filters
     *
     * @return QueryBuilder
     */
    public function getTaskListByTimeIntervalQueryBuilder(
        $userId,
        $startDate,
        $endDate,
        $filters = []
    ) {
        /** @var QueryBuilder $qb */
        $qb = $this->createQueryBuilder('t')
            ->select('t.id, t.subject, t.description, t.dueDate, t.createdAt, t.updatedAt')
            ->innerJoin('t.workflowStep', 'step')
            ->where('t.owner = :assignedTo AND step.name != :step')
            ->andWhere('t.dueDate >= :start AND t.dueDate <= :end')
            ->setParameter('assignedTo', $userId)
            ->setParameter('step', TaskRepository::CLOSED_STATE)
            ->setParameter('start', $startDate)
            ->setParameter('end', $endDate);
        if (is_array($filters)) {
            $newCriteria = new Criteria();
            foreach ($filters as $fieldName => $value) {
                $newCriteria->andWhere(Criteria::expr()->eq($fieldName, $value));
            }

            $filters = $newCriteria;
        }
        if ($filters) {
            $qb->addCriteria($filters);
        }

        return $qb;
    }
}
