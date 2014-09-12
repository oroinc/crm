<?php

namespace OroCRM\Bundle\SalesBundle\Migrations\Data\ORM;

use Doctrine\Common\Persistence\ObjectManager;

use OroCRM\Bundle\ChannelBundle\Entity\Channel;
use OroCRM\Bundle\ChannelBundle\Migrations\Data\ORM\AbstractDefaultChannelDataFixture;

class DefaultChannelData extends AbstractDefaultChannelDataFixture
{
    const B2B_CHANNEL_TYPE = 'b2b';

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $builder = $this->container->get('orocrm_channel.builder.factory')->createBuilder();
        $builder->setChannelType(self::B2B_CHANNEL_TYPE);
        $builder->setStatus(Channel::STATUS_ACTIVE);
        $builder->setEntities();

        $channel  = $builder->getChannel();
        $entities = $channel->getEntities();

        $shouldBeCreated = false;
        foreach ($entities as $entity) {
            $shouldBeCreated |= $this->getRowCount($entity);

            if ($shouldBeCreated) {
                break;
            }
        }

        if ($shouldBeCreated) {
            $this->em->persist($channel);
            $this->em->flush();

            // fill channel to all existing entities
            foreach ($entities as $entity) {
                $this->fillChannelToEntity($channel, $entity);
            }
        }
    }
}
