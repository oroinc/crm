<?php

namespace Oro\Bundle\SalesBundle\Provider;

use Oro\Bundle\AddressBundle\Provider\PhoneProviderInterface;
use Oro\Bundle\AddressBundle\Provider\RootPhoneProviderAwareInterface;
use Oro\Bundle\SalesBundle\Entity\Opportunity;

class OpportunityPhoneProvider implements PhoneProviderInterface, RootPhoneProviderAwareInterface
{
    /** @var PhoneProviderInterface */
    protected $rootProvider;

    /**
     * {@inheritdoc}
     */
    public function setRootProvider(PhoneProviderInterface $rootProvider)
    {
        $this->rootProvider = $rootProvider;
    }

    /**
     * Gets a phone number of the given Opportunity object
     *
     * @param Opportunity $object
     *
     * @return string|null
     */
    public function getPhoneNumber($object)
    {
        $contact = $object->getContact();
        if (!$contact) {
            return null;
        }

        return $this->rootProvider->getPhoneNumber($contact);
    }

    /**
     * Gets a list of all phone numbers available for the given Opportunity object
     *
     * @param Opportunity $object
     *
     * @return array of [phone number, phone owner]
     */
    public function getPhoneNumbers($object)
    {
        $contact = $object->getContact();
        if (!$contact) {
            return [];
        }

        return $this->rootProvider->getPhoneNumbers($contact);
    }
}
