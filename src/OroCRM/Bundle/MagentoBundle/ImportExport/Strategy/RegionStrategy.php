<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Strategy;

use Doctrine\ORM\UnitOfWork;

class RegionStrategy extends BaseStrategy
{
    const ENTITY_NAME = 'OroCRMMagentoBundle:Region';

    /**
     * {@inheritdoc}
     */
    public function process($entity)
    {
        $entity = $this->findAndReplaceEntity($entity, self::ENTITY_NAME, 'combinedCode');

        // validate and update context - increment counter or add validation error
        $entity = $this->validateAndUpdateContext($entity);

        return $entity;
    }

    /**
     * {@inheritdoc}
     */
    protected function validateAndUpdateContext($entity)
    {
        // validate contact
        $validationErrors = $this->strategyHelper->validateEntity($entity);
        if ($validationErrors) {
            $this->importExportContext->incrementErrorEntriesCount();
            $this->strategyHelper->addValidationErrors($validationErrors, $this->importExportContext);
            return null;
        }

        $uow = $this->strategyHelper->getEntityManager(self::ENTITY_NAME)->getUnitOfWork();
        // increment context counter
        if ($uow->getEntityState($entity, UnitOfWork::STATE_NEW) === UnitOfWork::STATE_NEW) {
            $this->importExportContext->incrementAddCount();
        } else {
            $this->importExportContext->incrementUpdateCount();
        }

        return $entity;
    }
}
