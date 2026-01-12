<?php

namespace Oro\Bundle\CaseBundle\Provider;

use Oro\Bundle\AddressBundle\Provider\PhoneProviderInterface;
use Oro\Bundle\AddressBundle\Provider\RootPhoneProviderAwareInterface;
use Oro\Bundle\CaseBundle\Entity\CaseEntity;

/**
 * Retrieves phone numbers for case entities from their associated contact's phone records.
 */
class CasePhoneProvider implements PhoneProviderInterface, RootPhoneProviderAwareInterface
{
    /** @var PhoneProviderInterface */
    protected $rootProvider;

    #[\Override]
    public function setRootProvider(PhoneProviderInterface $rootProvider)
    {
        $this->rootProvider = $rootProvider;
    }

    /**
     * Gets a phone number of the given Case object
     *
     * @param CaseEntity $object
     *
     * @return string|null
     */
    #[\Override]
    public function getPhoneNumber($object)
    {
        $contact = $object->getRelatedContact();
        if (!$contact) {
            return null;
        }

        return $this->rootProvider->getPhoneNumber($contact);
    }

    /**
     * Gets a list of all phone numbers available for the given Case object
     *
     * @param CaseEntity $object
     *
     * @return array of [phone number, phone owner]
     */
    #[\Override]
    public function getPhoneNumbers($object)
    {
        $relatedContact = $object->getRelatedContact();
        if (!$relatedContact) {
            return [];
        }

        return $this->rootProvider->getPhoneNumbers($relatedContact);
    }
}
