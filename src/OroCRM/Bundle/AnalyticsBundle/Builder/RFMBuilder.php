<?php

namespace OroCRM\Bundle\AnalyticsBundle\Builder;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use OroCRM\Bundle\AnalyticsBundle\Entity\RFMMetricCategory;
use Symfony\Component\PropertyAccess\PropertyAccess;

use OroCRM\Bundle\AnalyticsBundle\Model\AnalyticsAwareInterface;
use OroCRM\Bundle\AnalyticsBundle\Model\RFMAwareInterface;

class RFMBuilder implements AnalyticsBuilderInterface
{
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

        if (!in_array($type, RFMMetricCategory::$types)) {
            throw new \InvalidArgumentException(
                sprintf('Expected one of "" type, "" given', implode(',', RFMMetricCategory::$types, $type))
            );
        }

        $this->providers[] = $provider;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($entity)
    {
        return $entity instanceof RFMAwareInterface;
    }

    /**
     * @param RFMAwareInterface $entity
     *
     * {@inheritdoc}
     */
    public function build(AnalyticsAwareInterface $entity)
    {
        $propertyAccessor = PropertyAccess::createPropertyAccessor();

        foreach ($this->providers as $provider) {
            if ($provider->supports($entity)) {
                $value = $provider->getValue($entity);

                $propertyAccessor->setValue(
                    $entity,
                    $provider->getType(),
                    $this->getIndex($entity, $provider->getType(), $value)
                );

                return true;
            }
        }

        return false;
    }

    /**
     * @param AnalyticsAwareInterface $entity
     * @param string $type
     * @param int $value
     *
     * @return int|null
     */
    protected function getIndex(AnalyticsAwareInterface $entity, $type, $value)
    {
        $channelId = $this->doctrineHelper->getSingleEntityIdentifier($entity->getDataChannel());

        $categories = $this->getCategories($channelId, $type);
        if (!$categories) {
            return null;
        }

        foreach ($categories as $category) {
            if ($value > $category->getMinValue()) {
                return $category->getIndex();
            }
        }

        return null;
    }

    /**
     * @param string $channelId
     * @param string $type
     *
     * @return RFMMetricCategory[]
     */
    protected function getCategories($channelId, $type)
    {
        if (array_key_exists($channelId, $this->categories)) {
            return $this->categories[$channelId];
        }

        $categories = $this->doctrineHelper
            ->getEntityRepository('OroCRMAnalyticsBundle:RFMMetricCategory')
            ->findBy(['channel' => $channelId, 'type' => $type], ['maxValue' => 'ASC']);

        $this->categories[$channelId] = $categories;

        return $categories;
    }
}
