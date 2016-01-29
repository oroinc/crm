<?php

namespace OroCRM\Bundle\AnalyticsBundle\Builder;

use Doctrine\Common\Collections\Criteria;

use Doctrine\ORM\Mapping\ClassMetadata;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\BatchBundle\ORM\Query\BufferedQueryResultIterator;
use OroCRM\Bundle\AnalyticsBundle\Model\RFMAwareInterface;
use OroCRM\Bundle\ChannelBundle\Entity\Channel;
use OroCRM\Bundle\AnalyticsBundle\Entity\RFMMetricCategory;

class RFMBuilder implements AnalyticsBuilderInterface
{
    const BATCH_SIZE = 200;

    /**
     * @var DoctrineHelper
     */
    protected $doctrineHelper;

    /**
     * @var RFMProviderInterface[]
     */
    protected $providers = [];

    /**
     * @var array categories by channel
     */
    protected $categories = [];

    /**
     * @var array
     */
    protected $tablesNames = [];

    /**
     * @var array
     */
    protected $classesMetadata = [];

    /**
     * @var array
     */
    protected $tablesColumns = [];

    /**
     * @param DoctrineHelper $doctrineHelper
     */
    public function __construct(DoctrineHelper $doctrineHelper)
    {
        $this->doctrineHelper = $doctrineHelper;
    }

    /**
     * @param RFMProviderInterface $provider
     */
    public function addProvider(RFMProviderInterface $provider)
    {
        $type = $provider->getType();

        if (!in_array($type, RFMMetricCategory::$types, true)) {
            throw new \InvalidArgumentException(
                sprintf('Expected one of "%s" type, "%s" given', implode(',', RFMMetricCategory::$types), $type)
            );
        }

        $this->providers[] = $provider;
    }

    /**
     * @return RFMProviderInterface[]
     */
    public function getProviders()
    {
        return $this->providers;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(Channel $channel)
    {
        return is_a($channel->getCustomerIdentity(), 'OroCRM\Bundle\AnalyticsBundle\Model\RFMAwareInterface', true);
    }

    /**
     * {@inheritdoc}
     */
    public function build(Channel $channel, array $ids = [])
    {
        $data = $channel->getData();
        if (empty($data[RFMAwareInterface::RFM_STATE_KEY])
            || !filter_var($data[RFMAwareInterface::RFM_STATE_KEY], FILTER_VALIDATE_BOOLEAN)
        ) {
            return;
        }

        $iterator = $this->getEntityIdsByChannel($channel, $ids);

        $values = [];
        $count = 0;
        foreach ($iterator as $value) {
            $values[$value['id']] = $value;
            unset($values[$value['id']]['id']);
            $count++;
            if ($count % self::BATCH_SIZE === 0) {
                $this->processBatch($channel, $values);
                $values = [];
            }
        }
        $this->processBatch($channel, $values);
    }

    /**
     * @param Channel $channel
     * @param array $values
     */
    protected function processBatch(Channel $channel, array $values)
    {
        $toUpdate = [];
        foreach ($this->providers as $provider) {
            if (!$provider->supports($channel)) {
                continue;
            }
            $providerValues = $provider->getValues($channel, array_keys($values));

            $type = $provider->getType();

            foreach ($values as $id => $value) {
                $metric = isset($providerValues[$id]) ? $providerValues[$id] : null;
                $index = $this->getIndex($channel, $type, $metric);
                if ($index !== $value[$type]) {
                    $toUpdate[$id][$type] = $index;
                }
            }
        }
        $this->updateValues($channel, $toUpdate);
    }

    /**
     * @param Channel $channel
     * @param $values
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Exception
     */
    protected function updateValues(Channel $channel, $values)
    {
        if (empty($values)) {
            return;
        }
        $entityFQCN = $channel->getCustomerIdentity();

        $em = $this->doctrineHelper->getEntityManager($entityFQCN);
        $connection = $em->getConnection();
        $connection->beginTransaction();
        try {
            foreach ($values as $id => $value) {
                $metrics = $this->filterMetrics($entityFQCN, $value);
                if (empty($metrics)) {
                    continue;
                }
                $qb = $connection->createQueryBuilder();
                $qb->update($this->getTableName($entityFQCN), 'e');
                foreach ($metrics as $metricName => $metricValue) {
                    $qb->set($metricName, $metricValue);
                }
                $qb->where($qb->expr()->eq('e.id', '?'));
                $connection->executeUpdate($qb->getSQL(), [$id]);
            }
            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollBack();
            throw $e;
        }
    }

    /**
     * @param $entityFQCN
     * @param $value
     * @return array
     */
    protected function filterMetrics($entityFQCN, $value)
    {
        $metricNames = array_keys($value);
        $columnAliases = array_combine($this->getColumns($entityFQCN, $metricNames), array_values($value));
        return array_filter($columnAliases);
    }

    /**
     * @param Channel $channel
     * @param array $ids
     * @return \ArrayIterator|BufferedQueryResultIterator
     */
    protected function getEntityIdsByChannel(Channel $channel, array $ids = [])
    {
        $entityFQCN = $channel->getCustomerIdentity();

        $qb = $this->doctrineHelper->getEntityRepository($entityFQCN)->createQueryBuilder('e');

        $metrics = [];
        foreach ($this->providers as $provider) {
            if ($provider->supports($channel)) {
                $metrics[] = $provider->getType();
            }
        }

        if (empty($metrics)) {
            return new \ArrayIterator();
        }

        $qb->select(preg_filter('/^/', 'e.', $metrics))
            ->addSelect('e.id')
            ->andWhere('e.dataChannel = :dataChannel')
            ->orderBy(sprintf('e.%s', $this->doctrineHelper->getSingleEntityIdentifierFieldName($entityFQCN)))
            ->setParameter('dataChannel', $channel);

        if (!empty($ids)) {
            $qb->where($qb->expr()->in('c.id', ':ids'))
                ->setParameter('ids', $ids);
        }

        return (new BufferedQueryResultIterator($qb))->setBufferSize(self::BATCH_SIZE);
    }

    /**
     * @param Channel $channel
     * @param string $type
     * @param int $value
     *
     * @return int|null
     */
    protected function getIndex(Channel $channel, $type, $value)
    {
        $channelId = $this->doctrineHelper->getSingleEntityIdentifier($channel);
        if (!$channelId) {
            return null;
        }

        $categories = $this->getCategories($channelId, $type);
        if (!$categories) {
            return null;
        }

        // null value must be ranked with worse index
        if ($value === null) {
            /** @var RFMMetricCategory $category */
            $category = end($categories);
            reset($categories);
            return $category->getCategoryIndex();
        }

        // Search for RFM category that match current value
        foreach ($categories as $category) {
            $maxValue = $category->getMaxValue();
            if ($maxValue && $value > $maxValue) {
                continue;
            }

            $minValue = $category->getMinValue();
            if ($minValue !== null && $value <= $category->getMinValue()) {
                continue;
            }

            return $category->getCategoryIndex();
        }

        return null;
    }

    /**
     * @param int $channelId
     * @param string $type
     *
     * @return RFMMetricCategory[]
     */
    protected function getCategories($channelId, $type)
    {
        if (array_key_exists($channelId, $this->categories)
            && array_key_exists($type, $this->categories[$channelId])
        ) {
            return $this->categories[$channelId][$type];
        }

        $categories = $this->doctrineHelper
            ->getEntityRepository('OroCRMAnalyticsBundle:RFMMetricCategory')
            ->findBy(['channel' => $channelId, 'categoryType' => $type], ['categoryIndex' => Criteria::ASC]);

        $this->categories[$channelId][$type] = $categories;

        return $categories;
    }

    /**
     * @param string $className
     * @return string
     */
    protected function getTableName($className)
    {
        if (!isset($this->tablesNames[$className])) {
            $this->tablesNames[$className] = $this->getClassMetadata($className)->table['name'];
        }
        return $this->tablesNames[$className];
    }

    /**
     * @param string $className
     * @param array $fields
     * @return string
     */
    protected function getColumns($className, array $fields)
    {
        $result = [];

        foreach ($fields as $field) {
            if (!isset($this->tablesColumns[$className][$field])) {
                $this->tablesColumns[$className][$field] = $this->getClassMetadata($className)->getColumnName($field);
            }
            $result[] = $this->tablesColumns[$className][$field];
        }

        return $result;
    }

    /**
     * @param string $className
     * @return ClassMetadata
     */
    protected function getClassMetadata($className)
    {
        if (!isset($this->classesMetadata[$className])) {
            $this->classesMetadata[$className] = $this->doctrineHelper->getEntityManager($className)
                ->getClassMetadata($className);
        }

        return $this->classesMetadata[$className];
    }

}
