<?php

namespace OroCRM\Bundle\ChannelBundle\EventListener;

use Doctrine\ORM\EntityManager;
use Doctrine\Common\Persistence\ManagerRegistry;

use JMS\JobQueueBundle\Entity\Job;

use OroCRM\Bundle\ChannelBundle\Command\LifetimeAverageAggregateCommand;

use Oro\Bundle\ConfigBundle\Event\ConfigUpdateEvent;

class TimezoneChangeListener
{
    /** @var ManagerRegistry */
    protected $registry;

    /**
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * @param ConfigUpdateEvent $event
     */
    public function onConfigUpdate(ConfigUpdateEvent $event)
    {
        if (!$event->isChanged('oro_locale.timezone')) {
            return;
        }

        if (!$this->isAlreadyScheduled()) {
            /** @var EntityManager $em */
            $em = $this->registry->getManager();

            $job = new Job(LifetimeAverageAggregateCommand::COMMAND_NAME, ['-f']);
            $em->persist($job);
            $em->flush($job);
        }
    }

    /**
     * @return bool
     */
    protected function isAlreadyScheduled()
    {
        $schedule = $this->registry->getRepository('JMSJobQueueBundle:Job')
            ->findOneBy(['command' => LifetimeAverageAggregateCommand::COMMAND_NAME, 'state' => Job::STATE_PENDING]);

        return (bool) $schedule;
    }
}
