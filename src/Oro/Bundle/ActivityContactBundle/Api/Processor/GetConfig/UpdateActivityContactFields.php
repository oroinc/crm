<?php

namespace Oro\Bundle\ActivityContactBundle\Api\Processor\GetConfig;

use Oro\Bundle\ActivityContactBundle\EntityConfig\ActivityScope;
use Oro\Bundle\ActivityContactBundle\Model\TargetExcludeList;
use Oro\Bundle\ActivityContactBundle\Provider\ActivityContactProvider;
use Oro\Bundle\ApiBundle\Config\EntityDefinitionConfig;
use Oro\Bundle\ApiBundle\Processor\GetConfig\ConfigContext;
use Oro\Bundle\ApiBundle\Util\DoctrineHelper;
use Oro\Bundle\EntityConfigBundle\Config\ConfigManager;
use Oro\Component\ChainProcessor\ContextInterface;
use Oro\Component\ChainProcessor\ProcessorInterface;

/**
 * Renames "contacting activity" (ac_*) fields to have more readable names.
 * Makes these fields read-only for specified actions, e.g. "update",
 * because they are calculated automatically and should not be updated manually.
 */
class UpdateActivityContactFields implements ProcessorInterface
{
    private DoctrineHelper $doctrineHelper;
    private ConfigManager $configManager;
    private ActivityContactProvider $activityContactProvider;
    /** @var string[] */
    private array $excludedActions;

    /**
     * @param DoctrineHelper          $doctrineHelper
     * @param ConfigManager           $configManager
     * @param ActivityContactProvider $activityContactProvider
     * @param string[]                $excludedActions
     */
    public function __construct(
        DoctrineHelper $doctrineHelper,
        ConfigManager $configManager,
        ActivityContactProvider $activityContactProvider,
        array $excludedActions
    ) {
        $this->doctrineHelper = $doctrineHelper;
        $this->configManager = $configManager;
        $this->activityContactProvider = $activityContactProvider;
        $this->excludedActions = $excludedActions;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContextInterface $context): void
    {
        /** @var ConfigContext $context */

        $definition = $context->getResult();
        if (!$definition->isExcludeAll()) {
            // expected completed config
            return;
        }

        $entityClass = $context->getClassName();
        if (!$this->doctrineHelper->isManageableEntityClass($entityClass)) {
            // only manageable entities are supported
            return;
        }
        if (!$this->isSupportedEntity($entityClass)) {
            // an entity is not supported
            return;
        }

        $this->updateFields($definition, $context->getTargetAction());
    }

    private function isSupportedEntity(string $entityClass): bool
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

    private function updateFields(EntityDefinitionConfig $definition, ?string $targetAction): void
    {
        $renameMap = [
            ActivityScope::LAST_CONTACT_DATE     => 'lastContactedDate',
            ActivityScope::LAST_CONTACT_DATE_IN  => 'lastContactedDateIn',
            ActivityScope::LAST_CONTACT_DATE_OUT => 'lastContactedDateOut',
            ActivityScope::CONTACT_COUNT         => 'timesContacted',
            ActivityScope::CONTACT_COUNT_IN      => 'timesContactedIn',
            ActivityScope::CONTACT_COUNT_OUT     => 'timesContactedOut'
        ];
        foreach ($renameMap as $fieldName => $resultFieldName) {
            $field = $definition->getField($fieldName);
            if (null !== $field && !$definition->hasField($resultFieldName)) {
                if (!$field->hasPropertyPath()) {
                    $definition->removeField($fieldName);
                    $field->setPropertyPath($fieldName);
                    $definition->addField($resultFieldName, $field);
                }
                if (\in_array($targetAction, $this->excludedActions, true) && !$field->isExcluded()) {
                    $formOptions = $field->getFormOptions();
                    if (null === $formOptions || !\array_key_exists('mapped', $formOptions)) {
                        $formOptions['mapped'] = false;
                        $field->setFormOptions($formOptions);
                    }
                }
            }
        }
    }
}
