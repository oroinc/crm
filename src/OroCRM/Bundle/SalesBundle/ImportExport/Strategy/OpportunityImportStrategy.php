<?php

namespace OroCRM\Bundle\SalesBundle\ImportExport\Strategy;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\ImportExportBundle\Strategy\Import\ConfigurableAddOrReplaceStrategy;

use OroCRM\Bundle\SalesBundle\Entity\Opportunity;

class OpportunityImportStrategy extends ConfigurableAddOrReplaceStrategy
{
    /**
     * @var array
     */
    protected $probabilities;

    /**
     * @param ConfigManager $configManager
     * @return self
     */
    public function setConfigManager(ConfigManager $configManager)
    {
        $this->probabilities = $configManager->get(
            'oro_crm_sales.default_opportunity_probabilities'
        );

        return $this;
    }

    /**
     * {inheritdoc}
     */
    protected function afterProcessEntity($entity)
    {
        $this->setProbability($entity);

        return parent::afterProcessEntity($entity);
    }

    /**
     * @param Opportunity $entity
     */
    protected function setProbability(Opportunity $entity)
    {
        $status = $this->fieldHelper->getObjectValue($entity, 'status');
        if (!$status) {
            return;
        }

        // don't overwrite probability if already set
        $probability = $this->fieldHelper->getObjectValue($entity, 'probability');
        if ($probability) {
            return;
        }

        if (isset($this->probabilities[$status->getId()])) {
            $this->fieldHelper->setObjectValue(
                $entity,
                'probability',
                $this->probabilities[$status->getId()]
            );
        }
    }
}
