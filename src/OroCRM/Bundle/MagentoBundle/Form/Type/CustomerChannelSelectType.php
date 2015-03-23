<?php

namespace OroCRM\Bundle\MagentoBundle\Form\Type;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

use OroCRM\Bundle\MagentoBundle\Provider\ChannelType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use OroCRM\Bundle\ChannelBundle\Entity\Channel;

class CustomerChannelSelectType extends AbstractType
{
    const NAME = 'orocrm_magento_customer_channel_select';

    /**
     * @var string
     */
    protected $channelClass;

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
        return self::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'orocrm_channel_select_type';
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        if (!$this->channelClass) {
            throw new \InvalidArgumentException('Channel class is missing');
        }

        $queryBuilderNormalizer = function (Options $options, $qb) {
            /** @var EntityManager $em */
            $em = $options['em'];

            /** @var EntityRepository $repository */
            $repository = $em->getRepository($this->channelClass);
            $entities = $options->get('entities');

            /** @var QueryBuilder $queryBuilder */
            $queryBuilder = $qb($repository, $entities);
            $queryBuilder
                ->join('c.dataSource', 'd')
                ->andWhere(
                    $queryBuilder->expr()->andX(
                        $queryBuilder->expr()->eq('d.type', ':type'),
                        $queryBuilder->expr()->eq('d.enabled', ':enabled')
                    )
                )
                ->setParameter('type', ChannelType::TYPE)
                ->setParameter('enabled', true);

            $filteredQb = clone $queryBuilder;
            /** @var Channel[] $channels */
            $channels = $filteredQb->getQuery()->getResult();
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
        };

        $resolver->setNormalizers(['query_builder' => $queryBuilderNormalizer]);
    }
}
