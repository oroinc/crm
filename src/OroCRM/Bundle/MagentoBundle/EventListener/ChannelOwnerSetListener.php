<?php

namespace OroCRM\Bundle\MagentoBundle\EventListener;

use Doctrine\ORM\Query;
use Doctrine\ORM\EntityManager;

use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\IntegrationBundle\Event\DefaultOwnerSetEvent;

use OroCRM\Bundle\MagentoBundle\Provider\ChannelType;

/**
 * This event listener is subscribed on 'oro_integration.default_owner.set' in order to set default owner
 * to magento related entities.
 *
 * @package OroCRM\Bundle\MagentoBundle\EventListener
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

        // process only magento channels
        if ($channel->getType() !== ChannelType::TYPE) {
            return;
        }

        // update contacts related to current channel
        // skip if owner is already set manually
        $qb = $this->em->createQueryBuilder();
        $qb->update('OroCRMContactBundle:Contact', 'c')
            ->set('c.owner', $event->getDefaultUserOwner()->getId())
            ->where($qb->expr()->isNull('c.owner'))
            ->andWhere(
                $qb->expr()->exists(
                    $this->em->createQueryBuilder()
                        ->select('mc.id')
                        ->from('OroCRMMagentoBundle:Customer', 'mc')
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
        $qb->update('OroCRMAccountBundle:Account', 'a')
            ->set('a.owner', $event->getDefaultUserOwner()->getId())
            ->where($qb->expr()->isNull('a.owner'))
            ->andWhere(
                $qb->expr()->exists(
                    $this->em->createQueryBuilder()
                        ->select('mc.id')
                        ->from('OroCRMMagentoBundle:Customer', 'mc')
                        ->where('mc.channel = :channel')
                        ->setParameter('channel', $channel)
                        ->andWhere('mc.account = a.id')
                )
            )
            ->setParameter('channel', $channel);

        $qb->getQuery()->execute();

        $magentoEntities = ['OroCRMMagentoBundle:Customer', 'OroCRMMagentoBundle:Cart', 'OroCRMMagentoBundle:Order'];
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
            ->set('o.owner', $newOwnerId)
            ->where($qb->expr()->isNull('o.owner'))
            ->andWhere($qb->expr()->eq('o.channel', ':channel'))
            ->setParameter('channel', $channel);

        $qb->getQuery()->execute();
    }
}
