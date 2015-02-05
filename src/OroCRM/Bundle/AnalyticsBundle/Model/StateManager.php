<?php

namespace OroCRM\Bundle\AnalyticsBundle\Model;

use JMS\JobQueueBundle\Entity\Job;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use OroCRM\Bundle\AnalyticsBundle\Command\CalculateAnalyticsCommand;

class StateManager
{
    /**
     * @var DoctrineHelper
     */
    protected $doctrineHelper;

    /**
     * @param DoctrineHelper $doctrineHelper
     */
    public function __construct(DoctrineHelper $doctrineHelper)
    {
        $this->doctrineHelper = $doctrineHelper;
    }

    /**
     * @param string $args
     *
     * @return Job[]
     */
    public function getJob($args = null)
    {
        $qb = $this->doctrineHelper
            ->getEntityRepository('JMSJobQueueBundle:Job')
            ->createQueryBuilder('j');

        $qb
            ->where(
                $qb->expr()->andX(
                    $qb->expr()->eq('j.command', ':command'),
                    $qb->expr()->in('j.state', ':states')
                )
            )
            ->setParameters(
                [
                    'command' => CalculateAnalyticsCommand::COMMAND_NAME,
                    'states' => [Job::STATE_PENDING, Job::STATE_RUNNING]
                ]
            );

        if ($args) {
            $qb
                ->andWhere($qb->expr()->like('j.args', ':args'))
                ->setParameter('args', '%' . $args . '%');
        } else {
            $qb
                ->andWhere($qb->expr()->notLike('j.args', ':args'))
                ->setParameter('args', '%--channel%');
        }

        return $qb->getQuery()->getResult();
    }
}
