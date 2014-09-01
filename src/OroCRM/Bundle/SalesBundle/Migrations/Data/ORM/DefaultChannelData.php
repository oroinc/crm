<?php

namespace OroCRM\Bundle\SalesBundle\Migrations\Data\ORM;

use Doctrine\Common\Persistence\ObjectManager;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;

use OroCRM\Bundle\ChannelBundle\Entity\Channel;
use OroCRM\Bundle\ChannelBundle\Migrations\Data\ORM\AbstractDefaultChannelDataFixture;

class DefaultChannelData extends AbstractDefaultChannelDataFixture implements ContainerAwareInterface
{
    const B2B_CHANNEL_TYPE = 'b2b';

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $settingsProvider = $this->container->get('orocrm_channel.provider.settings_provider');

        $entities = $settingsProvider->getEntitiesByChannelType(self::B2B_CHANNEL_TYPE);
        $identity = $settingsProvider->getCustomerIdentityFromConfig(self::B2B_CHANNEL_TYPE);
        array_unshift($entities, $identity);
        $entities = array_unique($entities);

        $shouldBeCreated = false;
        foreach ($entities as $entity) {
            $shouldBeCreated |= $this->getRowCount($entity);

            if ($shouldBeCreated) {
                break;
            }
        }

        if ($shouldBeCreated) {
            $owner = $this->em->getRepository('OroOrganizationBundle:Organization')
                ->createQueryBuilder('o')
                ->setMaxResults(1)
                ->getQuery()
                ->getSingleResult();

            $channel = new Channel();
            $channel->setChannelType(self::B2B_CHANNEL_TYPE);
            $channel->setEntities($entities);
            $channel->setStatus(Channel::STATUS_ACTIVE);
            $channel->setCustomerIdentity($identity);
            $channel->setName(ucfirst(self::B2B_CHANNEL_TYPE . ' channel'));

            if ($owner) {
                $channel->setOwner($owner);
            }

            $this->em->persist($channel);
            $this->em->flush();

            // fill channel to all existing entities
            foreach ($entities as $entity) {
                $this->fillChannelToEntity($channel, $entity);
            }
        }
    }
}
