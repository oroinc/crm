<?php

namespace OroCRM\Bundle\AnalyticsBundle\EventListener;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManager;
use JMS\JobQueueBundle\Entity\Job;

use Oro\Bundle\ConfigBundle\Event\ConfigUpdateEvent;
use OroCRM\Bundle\AnalyticsBundle\Command\CalculateAnalyticsCommand;

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

        if (!$this->isAlreadyScheduled(CalculateAnalyticsCommand::COMMAND_NAME)) {
            /** @var EntityManager $em */
            $em = $this->registry->getManager();

            $job = new Job(CalculateAnalyticsCommand::COMMAND_NAME);
            $em->persist($job);
            $em->flush($job);
        }
    }

    /**
     * @param string $commandName
     * @return bool
     */
    protected function isAlreadyScheduled($commandName)
    {
        return (bool)$this->registry->getRepository('JMSJobQueueBundle:Job')
            ->findOneBy(['command' => $commandName, 'state' => Job::STATE_PENDING]);
    }
}
