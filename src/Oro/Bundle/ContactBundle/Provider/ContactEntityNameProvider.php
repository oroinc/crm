<?php

namespace Oro\Bundle\ContactBundle\Provider;

use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\LocaleBundle\Provider\EntityNameProvider;

class ContactEntityNameProvider extends EntityNameProvider
{
    /**
     * @var array Map of entity collection property and field name
     */
    public static $contactCollectionsMap = ['emails' => 'email', 'phones' => 'phone'];

    /**
     * {@inheritdoc}
     */
    public function getName($format, $locale, $entity)
    {
        return is_a($entity, Contact::class, true) ? parent::getName($format, $locale, $entity) : false;
    }

    /**
     * {@inheritdoc}
     */
    public function getNameDQL($format, $locale, $className, $alias)
    {
        if (!is_a($className, Contact::class, true)) {
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
        $subQuery = implode(', ', $subSelects);

        return sprintf('COALESCE(NULLIF(%s, \'\'), %s)', $nameDQL, $subQuery);
    }
}
