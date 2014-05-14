<?php

namespace OroCRM\Bundle\MagentoBundle\Migrations\Data\ORM;

use JMS\JobQueueBundle\Entity\Job;

use Doctrine\ORM\EntityRepository;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\IntegrationBundle\Command\SyncCommand;

class UpdateCustomerAddresses extends AbstractFixture
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        /** @var EntityRepository $repository */
        $repository = $manager->getRepository('OroCRMMagentoBundle:Address');

        $qb = $repository->createQueryBuilder('a');
        $qb->leftJoin('a.owner', 'c');
        $qb->leftJoin('c.channel', 'cc');
        $qb->select('DISTINCT cc.id');
        $qb->where($qb->expr()->isNull('a.originId'));
        $qb->andWhere($qb->expr()->isNotNull('cc.id'));

        $invalidEntriesAwareChannelIds = $qb->getQuery()->getArrayResult();

        /*
         * Find all invalid addresses and schedule force sync for them
         */
        foreach ($invalidEntriesAwareChannelIds as $channel) {
            $channelId = $channel['id'];
            $job       = new Job(
                SyncCommand::COMMAND_NAME,
                [
                    sprintf('--channel-id=%d', $channelId),
                    '--connector=customer',
                    '-v',
                    '--env=prod',
                    '--force'
                ]
            );

            $manager->persist($job);
        }

        $manager->flush();
    }
}
