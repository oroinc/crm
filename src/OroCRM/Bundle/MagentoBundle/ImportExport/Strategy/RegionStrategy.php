<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Strategy;

use Doctrine\ORM\UnitOfWork;

use OroCRM\Bundle\MagentoBundle\ImportExport\Serializer\RegionDenormalizer;

class RegionStrategy extends BaseStrategy
{
    /**
     * {@inheritdoc}
     */
    public function process($entity)
    {
        $exclude = [];

        if (!$entity->getName()) {
            // do not update name if it's empty, due to bug in magento API
            $exclude = ['name'];
        }
        $entity = $this->findAndReplaceEntity($entity, RegionDenormalizer::TYPE, 'combinedCode', $exclude);

        // validate and update context - increment counter or add validation error
        return $this->validateAndUpdateContext($entity);
    }

    /**
     * {@inheritdoc}
     */
    protected function validateAndUpdateContext($entity)
    {
        // validate contact
        $validationErrors = $this->strategyHelper->validateEntity($entity);
        if ($validationErrors) {
            $this->context->incrementErrorEntriesCount();
            $this->strategyHelper->addValidationErrors($validationErrors, $this->context);

            return null;
        }

        $uow = $this->strategyHelper->getEntityManager(RegionDenormalizer::TYPE)->getUnitOfWork();
        // increment context counter
        if ($uow->getEntityState($entity, UnitOfWork::STATE_NEW) === UnitOfWork::STATE_NEW) {
            $this->context->incrementAddCount();
        } else {
            $this->context->incrementUpdateCount();
        }

        return $entity;
    }
}
