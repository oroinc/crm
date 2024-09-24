<?php

namespace Oro\Bundle\ContactUsBundle\Provider;

use Oro\Bundle\ContactUsBundle\Entity\ContactReason;
use Oro\Bundle\EntityBundle\Provider\EntityNameProviderInterface;
use Oro\Bundle\LocaleBundle\Entity\Localization;
use Oro\Bundle\LocaleBundle\Entity\LocalizedFallbackValue;

/**
 * Provides a text representation of ContactReason entity.
 */
class ContactReasonEntityNameProvider implements EntityNameProviderInterface
{
    #[\Override]
    public function getName($format, $locale, $entity)
    {
        if (!$entity instanceof ContactReason) {
            return false;
        }

        $localizedTitle = $locale instanceof Localization
            ? (string)$entity->getTitle($locale)
            : null;

        return $localizedTitle ?: (string)$entity->getDefaultTitle();
    }

    #[\Override]
    public function getNameDQL($format, $locale, $className, $alias)
    {
        if (!is_a($className, ContactReason::class, true)) {
            return false;
        }

        if ($locale instanceof Localization) {
            return sprintf(
                'CAST((SELECT COALESCE(%1$s_t.string, %1$s_t.text, %1$s_dt.string, %1$s_dt.text) FROM %2$s %1$s_dt'
                . ' LEFT JOIN %2$s %1$s_t WITH %1$s_t MEMBER OF %1$s.titles AND %1$s_t.localization = %3$s'
                . ' WHERE %1$s_dt MEMBER OF %1$s.titles AND %1$s_dt.localization IS NULL) AS string)',
                $alias,
                LocalizedFallbackValue::class,
                $locale->getId()
            );
        }

        return sprintf(
            'CAST((SELECT COALESCE(%1$s_t.string, %1$s_t.text) FROM %2$s %1$s_t'
            . ' WHERE %1$s_t MEMBER OF %1$s.titles AND %1$s_t.localization IS NULL) AS string)',
            $alias,
            LocalizedFallbackValue::class
        );
    }
}
