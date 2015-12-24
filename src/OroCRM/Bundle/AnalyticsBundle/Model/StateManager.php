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
     * @param array  $states
     *
     * @return Job[]
     */
    public function getJob($args = null, array $states = [])
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
                    'states'  => $states ?: [Job::STATE_PENDING, Job::STATE_RUNNING]
                ]
            );

        if ($args) {
            $qb
                ->andWhere($qb->expr()->like('cast(j.args as text)', ':args'))
                ->setParameter('args', '%' . $args . '%');
        } else {
            $qb
                ->andWhere($qb->expr()->notLike('cast(j.args as text)', ':args'))
                ->setParameter('args', '%--channel%');
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @param string $args
     *
     * @return bool
     */
    public function isJobRunning($args = null)
    {
        return count($this->getJob($args, [Job::STATE_PENDING, Job::STATE_RUNNING])) > 1;
    }
}
