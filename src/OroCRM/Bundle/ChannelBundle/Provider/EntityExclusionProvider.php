<?php

namespace OroCRM\Bundle\ChannelBundle\Provider;

use Doctrine\ORM\Mapping\ClassMetadata;

use Oro\Bundle\EntityBundle\EntityConfig\BusinessScope;
use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;
use Oro\Bundle\EntityBundle\Provider\ExclusionProviderInterface;

class EntityExclusionProvider implements ExclusionProviderInterface
{
    /** @var ConfigProvider */
    protected $groupingConfigProvider;

    /**
     * @param ConfigProvider $groupingConfigProvider
     */
    public function __construct(ConfigProvider $groupingConfigProvider)
    {
        $this->groupingConfigProvider = $groupingConfigProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function isIgnoredEntity($className)
    {
        return $this->isIncludedByChannels($className);
    }

    /**
     * {@inheritdoc}
     */
    public function isIgnoredField(ClassMetadata $metadata, $fieldName)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function isIgnoredRelation(ClassMetadata $metadata, $associationName)
    {
        return $this->isIncludedByChannels($metadata->getAssociationTargetClass($associationName));
    }

    /**
     * @param $entityFQCN
     *
     * @return bool
     */
    protected function isIncludedByChannels($entityFQCN)
    {
        if (!$this->isBusinessEntity($entityFQCN)) {
            return false;
        }

        // @TODO check if it's in any integration

    }

    /**
     * Is given entity in "business entities" group
     *
     * @param string $entityFQCN - entity class name
     *
     * @return bool
     */
    protected function isBusinessEntity($entityFQCN)
    {
        $groups = $this->groupingConfigProvider->getConfig($entityFQCN)->get('groups');

        return in_array(BusinessScope::GROUP_BUSINESS, $groups, true);
    }
}
