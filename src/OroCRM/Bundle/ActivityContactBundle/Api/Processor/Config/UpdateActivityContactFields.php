<?php

namespace OroCRM\Bundle\ActivityContactBundle\Api\Processor\Config;

use Oro\Component\ChainProcessor\ContextInterface;
use Oro\Component\ChainProcessor\ProcessorInterface;
use Oro\Bundle\ApiBundle\Config\EntityDefinitionConfig;
use Oro\Bundle\ApiBundle\Processor\Config\ConfigContext;
use Oro\Bundle\EntityConfigBundle\Config\ConfigManager;
use OroCRM\Bundle\ActivityContactBundle\EntityConfig\ActivityScope;
use OroCRM\Bundle\ActivityContactBundle\Model\TargetExcludeList;
use OroCRM\Bundle\ActivityContactBundle\Provider\ActivityContactProvider;

/**
 * Renames "contacting activity" (ac_*) fields to have more readable names.
 * Exclude these fields for "update" action because they are calculated automatically
 * and should not be updated manually.
 */
class UpdateActivityContactFields implements ProcessorInterface
{
    /** @var ConfigManager */
    protected $configManager;

    /** @var  ActivityContactProvider */
    protected $activityContactProvider;

    /** @var string[] */
    protected $excludedActions;

    /**
     * @param ConfigManager           $configManager
     * @param ActivityContactProvider $activityContactProvider
     * @param string                  $excludedActions
     */
    public function __construct(
        ConfigManager $configManager,
        ActivityContactProvider $activityContactProvider,
        $excludedActions
    ) {
        $this->configManager = $configManager;
        $this->activityContactProvider = $activityContactProvider;
        $this->excludedActions = $excludedActions;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContextInterface $context)
    {
        /** @var ConfigContext $context */

        $definition = $context->getResult();
        if (!$definition->isExcludeAll()) {
            // expected completed config
            return;
        }

        $entityClass = $context->getClassName();
        if (!$this->isSupportedEntity($entityClass)) {
            // an entity is not supported
            return;
        }

        $this->updateFields($definition, $context->getTargetAction());
    }

    /**
     * @param string $entityClass
     *
     * @return bool
     */
    protected function isSupportedEntity($entityClass)
    {
        if (!$this->configManager->hasConfig($entityClass)) {
            // only extended entities are supported
            return false;
        }
        if (!$this->configManager->getEntityConfig('extend', $entityClass)->is('is_extend')) {
            // only extended entities are supported
            return false;
        }
        if (TargetExcludeList::isExcluded($entityClass)) {
            // skip excluded entities
            return false;
        }
        $activities = $this->configManager->getEntityConfig('activity', $entityClass)->get('activities');
        if (empty($activities)) {
            // entity should be associated with at least one activity
            return false;
        }
        $contactingActivities = $this->activityContactProvider->getSupportedActivityClasses();
        if (!array_intersect($contactingActivities, $activities)) {
            // an entity does not have supported activity
            return false;
        }

        return true;
    }

    /**
     * @param EntityDefinitionConfig $definition
     * @param string|null            $targetAction
     */
    protected function updateFields(EntityDefinitionConfig $definition, $targetAction)
    {
        $renameMap = [
            ActivityScope::LAST_CONTACT_DATE     => 'lastContactedDate',
            ActivityScope::LAST_CONTACT_DATE_IN  => 'lastContactedDateIn',
            ActivityScope::LAST_CONTACT_DATE_OUT => 'lastContactedDateOut',
            ActivityScope::CONTACT_COUNT         => 'timesContacted',
            ActivityScope::CONTACT_COUNT_IN      => 'timesContactedIn',
            ActivityScope::CONTACT_COUNT_OUT     => 'timesContactedOut',
        ];
        foreach ($renameMap as $fieldName => $resultFieldName) {
            if ($definition->hasField($fieldName) && !$definition->hasField($resultFieldName)) {
                $field = $definition->getField($fieldName);
                if (!$field->hasPropertyPath()) {
                    $definition->removeField($fieldName);
                    $field->setPropertyPath($fieldName);
                    $definition->addField($resultFieldName, $field);
                }
                if ('update' === $targetAction && !$field->hasExcluded() && !$field->isExcluded()) {
                    $field->setExcluded();
                }
            }
        }
    }
}
