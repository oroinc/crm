<?php

namespace Oro\Bundle\ContactBundle\Provider;

use Oro\Bundle\LocaleBundle\Provider\EntityNameProvider;

class ContactEntityNameProvider extends EntityNameProvider
{
    const CLASS_NAME = 'Oro\Bundle\ContactBundle\Entity\Contact';

    /**
     * @var array Map of entity collection property and field name
     */
    public static $contactCollectionsMap = ['phones' => 'phone', 'emails' => 'email'];

    /**
     * {@inheritdoc}
     */
    public function getName($format, $locale, $entity)
    {
        return is_a($entity, static::CLASS_NAME) ? parent::getName($format, $locale, $entity) : false;
    }

    /**
     * {@inheritdoc}
     */
    public function getNameDQL($format, $locale, $className, $alias)
    {
        if ($className !== self::CLASS_NAME) {
            return false;
        }

        $nameDQL = parent::getNameDQL($format, $locale, $className, $alias);

        if (!$nameDQL) {
            // unsupported format
            return false;
        }

        // fallback to email and phone if Contact name fields are empty
        $subSelects = [];

        foreach (self::$contactCollectionsMap as $property => $field) {
            // SubSelect to get the primary element from the collection
            $subSelects[] = sprintf(
                'CAST((' .
                'SELECT %1$s_%3$s.%4$s FROM %2$s %1$s_%3$s_base' .
                ' LEFT JOIN %1$s_%3$s_base.%3$s %1$s_%3$s' .
                ' WHERE %1$s_%3$s.primary = true AND %1$s_%3$s_base = %1$s' .
                ') AS string)',
                $alias,
                $className,
                $property,
                $field
            );
        }
        $subQuery = join(', ', $subSelects);

        return sprintf('COALESCE(NULLIF(%s, \'\'), %s)', $nameDQL, $subQuery);
    }
}
