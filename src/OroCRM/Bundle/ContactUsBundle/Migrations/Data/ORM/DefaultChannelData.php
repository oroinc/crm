<?php

namespace OroCRM\Bundle\ContactUsBundle\Migrations\Data\ORM;

use Doctrine\Common\Persistence\ObjectManager;

use OroCRM\Bundle\ChannelBundle\Entity\Channel;
use OroCRM\Bundle\ChannelBundle\Migrations\Data\ORM\AbstractDefaultChannelDataFixture;

class DefaultChannelData extends AbstractDefaultChannelDataFixture
{
    const CHANNEL_TYPE = 'custom';

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $entity = 'OroCRM\Bundle\ContactUsBundle\Entity\ContactRequest';

        $shouldBeCreated = $this->getRowCount($entity);
        if ($shouldBeCreated) {
            /** @var Channel $channel */
            $channel = $this->em->getRepository('OroCRMChannelBundle:Channel')
                ->createQueryBuilder('c')
                ->andWhere('c.channelType = :type')
                ->setParameter('type', self::CHANNEL_TYPE)
                ->setMaxResults(1)
                ->getQuery()
                ->getOneOrNullResult();

            if (!$channel) {
                $builder = $this->container->get('orocrm_channel.builder.factory')->createBuilder();
            } else {
                $builder = $this->container->get('orocrm_channel.builder.factory')->createBuilderForChannel($channel);
            }

            $builder->setStatus(Channel::STATUS_ACTIVE);
            $builder->addEntity($entity);

            $channel = $builder->getChannel();

            $this->em->persist($channel);
            $this->em->flush();
            $this->fillChannelToEntity($channel, $entity);
        }
    }
}
