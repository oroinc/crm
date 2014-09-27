<?php

namespace OroCRM\Bundle\ChannelBundle\EventListener;

use Doctrine\ORM\EntityManager;

use JMS\JobQueueBundle\Entity\Job;

use Symfony\Bridge\Doctrine\RegistryInterface;

use OroCRM\Bundle\ChannelBundle\Command\LifetimeAverageAggregateCommand;

use Oro\Bundle\ConfigBundle\Event\ConfigUpdateEvent;

class TimezoneChangeListener
{
    /** @var RegistryInterface */
    protected $registry;

    /**
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
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

        /** @var EntityManager $em */
        $em = $this->registry->getManager();

        $job = new Job(LifetimeAverageAggregateCommand::COMMAND_NAME, ['-f']);
        $em->persist($job);
        $em->flush($job);
    }
}
