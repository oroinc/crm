<?php

namespace Oro\Bundle\MagentoBundle\EventListener;

use Doctrine\ORM\EntityManager;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\IntegrationBundle\Event\DefaultOwnerSetEvent;
use Oro\Bundle\MagentoBundle\Provider\MagentoChannelType;

/**
 * This event listener is subscribed on 'oro_integration.default_owner.set' in order to set default owner
 * to magento related entities.
 *
 * @package Oro\Bundle\MagentoBundle\EventListener
 */
class ChannelOwnerSetListener
{
    /** @var EntityManager */
    protected $em;

    /**
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @param DefaultOwnerSetEvent $event
     */
    public function onSet(DefaultOwnerSetEvent $event)
    {
        $channel = $event->getChannel();

        /**
         * @todo Remove dependency on exact magento channel type in CRM-8153
         */
        // process only magento channels
        if ($channel->getType() !== MagentoChannelType::TYPE) {
            return;
        }

        // update contacts related to current channel
        // skip if owner is already set manually
        $qb = $this->em->createQueryBuilder();
        $qb->update('OroContactBundle:Contact', 'c')
            ->set('c.owner', $event->getDefaultUserOwner()->getId())
            ->where($qb->expr()->isNull('c.owner'))
            ->andWhere(
                $qb->expr()->exists(
                    $this->em->createQueryBuilder()
                        ->select('mc.id')
                        ->from('OroMagentoBundle:Customer', 'mc')
                        ->where('mc.channel = :channel')
                        ->setParameter('channel', $channel)
                        ->andWhere('mc.contact = c.id')
                )
            )
            ->setParameter('channel', $channel);

        $qb->getQuery()->execute();

        // update accounts related to current channel
        // skip if owner is already set manually
        $qb = $this->em->createQueryBuilder();
        $qb->update('OroAccountBundle:Account', 'a')
            ->set('a.owner', $event->getDefaultUserOwner()->getId())
            ->where($qb->expr()->isNull('a.owner'))
            ->andWhere(
                $qb->expr()->exists(
                    $this->em->createQueryBuilder()
                        ->select('mc.id')
                        ->from('OroMagentoBundle:Customer', 'mc')
                        ->where('mc.channel = :channel')
                        ->setParameter('channel', $channel)
                        ->andWhere('mc.account = a.id')
                )
            )
            ->setParameter('channel', $channel);

        $qb->getQuery()->execute();

        $magentoEntities = ['OroMagentoBundle:Customer', 'OroMagentoBundle:Cart', 'OroMagentoBundle:Order'];
        foreach ($magentoEntities as $entity) {
            $this->updateMagentoEntity($entity, $channel, $event->getDefaultUserOwner()->getId());
        }
    }

    /**
     * Update magento entities, skip if owner is already set manually
     *
     * @param string  $entityName
     * @param Channel $channel
     * @param int     $newOwnerId
     */
    protected function updateMagentoEntity($entityName, Channel $channel, $newOwnerId)
    {
        $qb = $this->em->createQueryBuilder();
        $qb->update($entityName, 'o')
            ->set('o.owner', ':newOwnerId')
            ->where($qb->expr()->isNull('o.owner'))
            ->andWhere($qb->expr()->eq('o.channel', ':channel'))
            ->setParameter('newOwnerId', $newOwnerId)
            ->setParameter('channel', $channel);

        $qb->getQuery()->execute();
    }
}
