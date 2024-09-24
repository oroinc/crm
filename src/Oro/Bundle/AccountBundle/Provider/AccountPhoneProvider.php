<?php

namespace Oro\Bundle\AccountBundle\Provider;

use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\AddressBundle\Provider\PhoneProviderInterface;
use Oro\Bundle\AddressBundle\Provider\RootPhoneProviderAwareInterface;

class AccountPhoneProvider implements PhoneProviderInterface, RootPhoneProviderAwareInterface
{
    /** @var PhoneProviderInterface */
    protected $rootProvider;

    #[\Override]
    public function setRootProvider(PhoneProviderInterface $rootProvider)
    {
        $this->rootProvider = $rootProvider;
    }

    /**
     * Gets a phone number of the given Account object
     *
     * @param Account $object
     *
     * @return string|null
     */
    #[\Override]
    public function getPhoneNumber($object)
    {
        $defaultContact = $object->getDefaultContact();
        if (!$defaultContact) {
            return null;
        }

        return $this->rootProvider->getPhoneNumber($defaultContact);
    }

    /**
     * Gets a list of all phone numbers available for the given Account object
     *
     * @param Account $object
     *
     * @return array of [phone number, phone owner]
     */
    #[\Override]
    public function getPhoneNumbers($object)
    {
        $defaultContact = $object->getDefaultContact();
        $result         = $defaultContact ? $this->rootProvider->getPhoneNumbers($defaultContact) : [];
        foreach ($object->getContacts() as $contact) {
            if ($contact !== $defaultContact) {
                $result = array_merge($result, $this->rootProvider->getPhoneNumbers($contact));
            }
        }

        return $result;
    }
}
