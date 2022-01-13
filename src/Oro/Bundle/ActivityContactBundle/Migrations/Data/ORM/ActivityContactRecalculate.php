<?php

namespace Oro\Bundle\ActivityContactBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\ActivityContactBundle\Command\ActivityContactRecalculateCommand;
use Oro\Bundle\CronBundle\Async\Topic\RunCommandTopic;
use Oro\Component\MessageQueue\Client\MessageProducerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Produces message to recalculate contacting activities.
 */
class ActivityContactRecalculate extends AbstractFixture implements ContainerAwareInterface
{
    /** @var ContainerInterface */
    protected $container;

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        /** @var MessageProducerInterface $producer */
        $producer = $this->container->get('oro_message_queue.client.message_producer');
        $producer->send(RunCommandTopic::getName(), [
            'command' => ActivityContactRecalculateCommand::getDefaultName(),
            'arguments' => ['-v' => 1, '--disabled-listeners' => ['all']]
        ]);
    }
}
