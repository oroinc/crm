<?php

namespace OroCRM\Bundle\SalesBundle\Provider;

use Oro\Bundle\AddressBundle\Provider\PhoneProviderInterface;
use Oro\Bundle\AddressBundle\Provider\RootPhoneProviderAwareInterface;

use OroCRM\Bundle\SalesBundle\Entity\B2bCustomer;

class B2bCustomerPhoneProvider implements PhoneProviderInterface, RootPhoneProviderAwareInterface
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
     * Gets a phone number of the given B2bCustomer object
     *
     * @param B2bCustomer $object
     *
     * @return string|null
     */
    public function getPhoneNumber($object)
    {
        $phone = null;
        $primaryPhone = $object->getPrimaryPhone();

        if ($primaryPhone) {
            $phone = $primaryPhone->getPhone();
        } else {
            $contact = $object->getContact();
            if ($contact) {
                $phone = $this->rootProvider->getPhoneNumber($contact);
            }
        }

        return $phone;
    }

    /**
     * Gets a list of all phone numbers available for the given B2bCustomer object
     *
     * @param B2bCustomer $object
     *
     * @return array of [phone number, phone owner]
     */
    public function getPhoneNumbers($object)
    {
        $result = [];
        foreach ($object->getPhones() as $phone) {
            $result[] = [$phone->getPhone(), $object];
        }

        if (!$result) {
            $contact = $object->getContact();
            if ($contact) {
                $result = $this->rootProvider->getPhoneNumbers($contact);
            }
        }
        
        return $result;
    }
}
