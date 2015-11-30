<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Strategy;

use Oro\Bundle\ImportExportBundle\Strategy\Import\ConfigurableAddOrReplaceStrategy;
use Doctrine\Common\Util\ClassUtils;

class DefaultMagentoImportStrategy extends ConfigurableAddOrReplaceStrategy
{
    /**
     * {@inheritdoc}
     */
    protected function updateContextCounters($entity)
    {
        // increment context counter
        $identifier = $this->databaseHelper->getIdentifier($entity);
        if ($identifier) {
            $this->context->incrementUpdateCount();
        } else {
            $this->context->incrementAddCount();
        }

        return $entity;
    }

    /**
     * {@inheritdoc}
     */
    protected function findExistingEntity($entity, array $searchContext = [])
    {
        $entityName = ClassUtils::getClass($entity);
        $identifier = $this->databaseHelper->getIdentifier($entity);
        $existingEntity = null;

        // find by identifier
        if ($identifier) {
            $existingEntity = $this->databaseHelper->find($entityName, $identifier);
        }

        // find by identity fields
        if (!$existingEntity
            && (!$searchContext || $this->databaseHelper->getIdentifier(current($searchContext)))
        ) {
            $identityValues = $searchContext;

            $usedTraits = class_uses($entityName);
            $targetTrait = 'OroCRM\Bundle\MagentoBundle\Entity\IntegrationEntityTrait';
            if ($this->context->hasOption('channel') && in_array($targetTrait, $usedTraits)) {
                $channel = $this->databaseHelper->findOneBy(
                    'Oro\Bundle\IntegrationBundle\Entity\Channel',
                    ['id' => $this->context->getOption('channel')]
                );
                $identityValues['channel'] = $channel;
            }

            $identityValues += $this->fieldHelper->getIdentityValues($entity);
            $existingEntity = $this->findEntityByIdentityValues($entityName, $identityValues);
        }

        if ($existingEntity && !$identifier) {
            $identifier = $this->databaseHelper->getIdentifier($existingEntity);
            $identifierName = $this->databaseHelper->getIdentifierFieldName($entity);
            $this->fieldHelper->setObjectValue($entity, $identifierName, $identifier);
        }

        return $existingEntity;
    }
}
