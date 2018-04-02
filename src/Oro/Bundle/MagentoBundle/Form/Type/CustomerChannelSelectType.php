<?php

namespace Oro\Bundle\MagentoBundle\Form\Type;

use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\ChannelBundle\Form\Type\ChannelSelectType;
use Oro\Bundle\ChannelBundle\Provider\ChannelsByEntitiesProvider;
use Oro\Bundle\MagentoBundle\Provider\MagentoChannelType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CustomerChannelSelectType extends AbstractType
{
    const NAME = 'oro_magento_customer_channel_select';

    /**
     * @var string
     */
    protected $channelClass;

    /**
     * @var ChannelsByEntitiesProvider
     */
    protected $channelsProvider;

    /**
     * @param ChannelsByEntitiesProvider $channelsProvider
     */
    public function __construct(ChannelsByEntitiesProvider $channelsProvider)
    {
        $this->channelsProvider = $channelsProvider;
    }

    /**
     * @param string $channelClass
     * @return CustomerChannelSelectType
     */
    public function setChannelClass($channelClass)
    {
        $this->channelClass = $channelClass;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->getBlockPrefix();
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return self::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return ChannelSelectType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        if (!$this->channelClass) {
            throw new \InvalidArgumentException('Channel class is missing');
        }

        $resolver->setNormalizer(
            'query_builder',
            function (Options $options, $value) {
                $entities     = $options['entities'];
                $queryBuilder = $this->channelsProvider->getChannelsByEntitiesQB($entities);

                $queryBuilder
                    ->join('c.dataSource', 'd')
                    ->andWhere(
                        $queryBuilder->expr()->andX(
                            $queryBuilder->expr()->eq('d.type', ':type'),
                            $queryBuilder->expr()->eq('d.enabled', ':enabled')
                        )
                    )
                    /**
                     * @todo Remove dependency on exact magento channel type in CRM-8153
                     */
                    ->setParameter('type', MagentoChannelType::TYPE)
                    ->setParameter('enabled', true);

                $filteredQb = clone $queryBuilder;
                /** @var Channel[] $channels */
                $channels     = $filteredQb->getQuery()->getResult();
                $skipEntities = [];
                foreach ($channels as $channel) {
                    $dataSource = $channel->getDataSource();
                    if (!(bool)$dataSource->getSynchronizationSettings()->offsetGetOr('isTwoWaySyncEnabled')) {
                        $skipEntities[] = $channel->getId();
                    }
                }

                if ($skipEntities) {
                    $queryBuilder->andWhere($queryBuilder->expr()->notIn('c.id', ':skipEntities'))
                        ->setParameter('skipEntities', $skipEntities);
                }

                return $queryBuilder;
            }
        );
    }
}
