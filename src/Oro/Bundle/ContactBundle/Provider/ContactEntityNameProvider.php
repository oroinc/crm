<?php

namespace Oro\Bundle\ContactBundle\Provider;

use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\EntityBundle\Provider\EntityNameProviderInterface;
use Oro\Bundle\LocaleBundle\DQL\DQLNameFormatter;
use Oro\Bundle\LocaleBundle\Entity\Localization;
use Oro\Bundle\LocaleBundle\Formatter\NameFormatter;
use Oro\Component\DependencyInjection\ServiceLink;

/**
 * Provides a text representation of Contact entity.
 */
class ContactEntityNameProvider implements EntityNameProviderInterface
{
    private ServiceLink $nameFormatterLink;
    private ServiceLink $dqlNameFormatterLink;

    public function __construct(ServiceLink $nameFormatterLink, ServiceLink $dqlNameFormatterLink)
    {
        $this->nameFormatterLink = $nameFormatterLink;
        $this->dqlNameFormatterLink = $dqlNameFormatterLink;
    }

    /**
     * {@inheritDoc}
     */
    public function getName($format, $locale, $entity)
    {
        if (!$entity instanceof Contact || self::FULL !== $format) {
            return false;
        }

        /** @var NameFormatter $nameFormatter */
        $nameFormatter = $this->nameFormatterLink->getService();
        $name = $nameFormatter->format(
            $entity,
            $locale instanceof Localization ? $locale->getLanguageCode() : $locale
        );
        if ($name) {
            return $name;
        }

        return (string)($entity->getPrimaryEmail() ?: $entity->getPrimaryPhone());
    }

    /**
     * {@inheritDoc}
     */
    public function getNameDQL($format, $locale, $className, $alias)
    {
        if (!is_a($className, Contact::class, true) || self::FULL !== $format) {
            return false;
        }

        /** @var DQLNameFormatter $dqlNameFormatter */
        $dqlNameFormatter = $this->dqlNameFormatterLink->getService();
        $nameDQL = $dqlNameFormatter->getFormattedNameDQL(
            $alias,
            $className,
            $locale instanceof Localization ? $locale->getLanguageCode() : $locale
        );

        return sprintf(
            'COALESCE(NULLIF(%s, \'\'), %s, %s, \'\')',
            $nameDQL,
            $this->getPrimaryEntitySubSelect($alias, 'emails', 'email'),
            $this->getPrimaryEntitySubSelect($alias, 'phones', 'phone')
        );
    }

    private function getPrimaryEntitySubSelect(string $alias, string $property, string $field): string
    {
        return sprintf(
            'CAST((SELECT %1$s_%3$s.%4$s FROM %2$s %1$s_%3$s_base LEFT JOIN %1$s_%3$s_base.%3$s %1$s_%3$s'
            . ' WHERE %1$s_%3$s.primary = true AND %1$s_%3$s_base = %1$s) AS string)',
            $alias,
            Contact::class,
            $property,
            $field
        );
    }
}
