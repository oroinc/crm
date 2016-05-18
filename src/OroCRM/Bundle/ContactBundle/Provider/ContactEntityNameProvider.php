<?php

namespace OroCRM\Bundle\ContactBundle\Provider;

use Oro\Bundle\LocaleBundle\Provider\EntityNameProvider;

class ContactEntityNameProvider extends EntityNameProvider
{
    const CLASS_NAME = 'OroCRM\Bundle\ContactBundle\Entity\Contact';

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
        return $className === self::CLASS_NAME ? parent::getNameDQL($format, $locale, $className, $alias) : false;
    }
}
