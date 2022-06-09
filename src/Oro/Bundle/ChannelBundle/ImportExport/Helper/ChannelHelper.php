<?php

namespace Oro\Bundle\ChannelBundle\ImportExport\Helper;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\IntegrationBundle\Entity\Channel as Integration;

/**
 * The utility class to get an integration channel.
 */
class ChannelHelper
{
    private ManagerRegistry $doctrine;
    private array $integrationToChannelMap = [];

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    public function getChannel(Integration $integration, bool $optional = false): ?Channel
    {
        $this->ensureInitialized();

        if (isset($this->integrationToChannelMap[$integration->getId()])) {
            return $this->getEntityManager()->getPartialReference(
                Channel::class,
                $this->integrationToChannelMap[$integration->getId()]
            );
        }
        if (!$optional) {
            throw new \LogicException('Unable to find channel for given integration');
        }

        return null;
    }

    private function ensureInitialized(): void
    {
        $qb = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('c.id, i.id as integrationId')
            ->from(Channel::class, 'c')
            ->innerJoin('c.dataSource', 'i');

        $result = $qb->getQuery()->getArrayResult();
        foreach ($result as $row) {
            $this->integrationToChannelMap[$row['integrationId']] = $row['id'];
        }
    }

    private function getEntityManager(): EntityManagerInterface
    {
        return $this->doctrine->getManager();
    }
}
