<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Strategy;

use OroCRM\Bundle\MagentoBundle\ImportExport\Strategy\StrategyHelper\DoctrineHelper;
use OroCRM\Bundle\MagentoBundle\Provider\MagentoConnectorInterface;

class RegionStrategy extends AbstractImportStrategy
{
    /**
     * @var DoctrineHelper
     */
    protected $doctrineHelper;

    /**
     * @param DoctrineHelper $doctrineHelper
     */
    public function setDoctrineHelper($doctrineHelper)
    {
        $this->doctrineHelper = $doctrineHelper;
    }

    /**
     * {@inheritdoc}
     */
    protected function processEntity(
        $entity,
        $isFullData = false,
        $isPersistNew = false,
        $itemData = null,
        array $searchContext = [],
        $entityIsRelation = false
    ) {
        $excluded = [];

        if (!$entity->getName()) {
            // do not update name if it's empty, due to bug in magento API
            $excluded = ['name'];
        }

        return $this->doctrineHelper
            ->findAndReplaceEntity($entity, MagentoConnectorInterface::REGION_TYPE, 'combinedCode', $excluded);
    }
}
