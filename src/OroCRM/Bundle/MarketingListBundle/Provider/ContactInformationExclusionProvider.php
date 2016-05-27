<?php

namespace OroCRM\Bundle\MarketingListBundle\Provider;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Mapping\ClassMetadata;

use Oro\Bundle\EntityBundle\Provider\AbstractExclusionProvider;
use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;

/**
 * Provide exclude logic to filter entities with "contact_information" data
 */
class ContactInformationExclusionProvider extends AbstractExclusionProvider
{
    /**
     * @var ConfigProvider
     */
    protected $entityConfigProvider;

    /**
     * @var ManagerRegistry
     */
    protected $managerRegistry;

    /**
     * @param ConfigProvider  $entityConfigProvider
     * @param ManagerRegistry $managerRegistry
     */
    public function __construct(ConfigProvider $entityConfigProvider, ManagerRegistry $managerRegistry)
    {
        $this->entityConfigProvider = $entityConfigProvider;
        $this->managerRegistry      = $managerRegistry;
    }

    /**
     * {@inheritdoc}
     */
    public function isIgnoredEntity($className)
    {
        if (!$this->entityConfigProvider->hasConfig($className)) {
            return true;
        }

        $entityConfig = $this->entityConfigProvider->getConfig($className);
        if ($entityConfig->has('contact_information')) {
            return false;
        }

        /** @var ClassMetadata $metadata */
        $metadata = $this->managerRegistry->getManagerForClass($className)->getClassMetadata($className);
        foreach ($metadata->getFieldNames() as $fieldName) {
            if ($this->entityConfigProvider->hasConfig($className, $fieldName)) {
                $fieldConfig = $this->entityConfigProvider->getConfig($className, $fieldName);
                if ($fieldConfig->has('contact_information')) {
                    return false;
                }
            }
        }

        return true;
    }
}
