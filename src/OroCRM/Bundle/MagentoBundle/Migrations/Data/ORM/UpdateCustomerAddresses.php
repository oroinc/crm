<?php

namespace OroCRM\Bundle\MagentoBundle\Migrations\Data\ORM;

use Doctrine\ORM\EntityRepository;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\IntegrationBundle\Manager\GenuineSyncScheduler;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class UpdateCustomerAddresses extends AbstractFixture implements ContainerAwareInterface
{
    use ContainerAwareTrait;

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
            $this->getSyncScheduler()->schedule($channel['id'], 'customer', [
                'force' => true
            ]);
        }

        $manager->flush();
    }

    /**
     * @return GenuineSyncScheduler
     */
    private function getSyncScheduler()
    {
        return $this->container->get('oro_integration.genuine_sync_scheduler');
    }
}
