<?php

namespace Oro\Bundle\ChannelBundle\ImportExport\Helper;

use Doctrine\ORM\EntityManager;
use Doctrine\Common\Persistence\ManagerRegistry;

use Oro\Bundle\IntegrationBundle\Entity\Channel as Integration;
use Oro\Bundle\ChannelBundle\Entity\Channel;

class ChannelHelper
{
    /** @var ManagerRegistry */
    protected $registry;

    /** @var null|array */
    protected $integrationToChannelMap;

    /**
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * @param Integration $integration
     *
     * @param bool        $optional
     *
     * @throws \LogicException
     * @return null|Channel
     */
    public function getChannel(Integration $integration, $optional = false)
    {
        $this->ensureInitialized();

        if (isset($this->integrationToChannelMap[$integration->getId()])) {
            /** @var EntityManager $em */
            $em = $this->registry->getManager();
            $id = $this->integrationToChannelMap[$integration->getId()];

            $channel = $em->getPartialReference('OroChannelBundle:Channel', $id);

            return $channel;
        } elseif (!$optional) {
            throw new \LogicException('Unable to find channel for given integration');
        }

        return null;
    }

    /**
     * Initialize map from database
     */
    protected function ensureInitialized()
    {
        /** @var EntityManager $em */
        $em = $this->registry->getManager();
        $qb = $em->createQueryBuilder()
            ->select('c.id, i.id as integrationId')
            ->from('OroChannelBundle:Channel', 'c')
            ->innerJoin('c.dataSource', 'i');

        $result = $qb->getQuery()->getArrayResult();

        foreach ($result as $row) {
            $this->integrationToChannelMap[$row['integrationId']] = $row['id'];
        }
    }
}
