<?php

namespace Oro\Bundle\SalesBundle\Provider\Customer\AccountAutocomplete;

interface AccountAutocompleteProviderInterface
{
    /**
     * Check provider supported entity
     *
     * @param $entity
     *
     * @return bool
     */
    public function isSupportEntity($entity);

    /**
     * Returns array of emails
     *
     * @param object $entity customer entity
     *
     * @return array
     */
    public function getEmails($entity);

    /**
     * Returns array of phones
     *
     * @param object $entity customer entity
     *
     * @return array
     */
    public function getPhones($entity);

    /**
     * Returns array of names
     *
     * @param object $entity customer entity
     *
     * @return array
     */
    public function getNames($entity);
}
