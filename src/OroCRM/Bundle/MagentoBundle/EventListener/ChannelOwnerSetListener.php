<?php

namespace OroCRM\Bundle\MagentoBundle\EventListener;

use Doctrine\ORM\EntityManager;
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
            ->set('c.owner', $event->getDefaultUserOwner())
            ->where($qb->expr()->isNull('c.owner'))
            ->andWhere(
                $qb->expr()->exists(
                    $this->em->getRepository('OroCRMMagentoBundle:Customer')->createQueryBuilder('mc')
                        ->innerJoin('mc.contact', 'mcc')
                )
            );

        echo $qb->getQuery()->getSQL();die;
    }
}
