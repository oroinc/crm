<?php

namespace Oro\Bundle\MagentoBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityRepository;
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
        $repository = $manager->getRepository('OroMagentoBundle:Address');

        $qb = $repository->createQueryBuilder('a');
        $qb->leftJoin('a.owner', 'c');
        $qb->leftJoin('c.channel', 'cc');
        $qb->select('DISTINCT cc.id');
        $qb->where($qb->expr()->isNull('a.originId'));
        $qb->andWhere($qb->expr()->isNotNull('cc.id'));

        $invalidEntriesAwareIntegrationIds = $qb->getQuery()->getArrayResult();

        /*
         * Find all invalid addresses and schedule force sync for them
         */
        foreach ($invalidEntriesAwareIntegrationIds as $integration) {
            $this->getSyncScheduler()->schedule($integration['id'], 'customer', [
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
