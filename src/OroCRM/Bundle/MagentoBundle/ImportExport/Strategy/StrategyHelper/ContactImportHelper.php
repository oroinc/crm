<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Strategy\StrategyHelper;

use Doctrine\Common\Collections\ArrayCollection;

use Symfony\Component\PropertyAccess\PropertyAccessor;

use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\AddressBundle\Entity\AbstractAddress;
use Oro\Bundle\IntegrationBundle\Provider\TwoWaySyncConnectorInterface;

use OroCRM\Bundle\MagentoBundle\Entity\Address;
use OroCRM\Bundle\MagentoBundle\Entity\Customer;
use OroCRM\Bundle\ContactBundle\Entity\Contact;
use OroCRM\Bundle\ContactBundle\Entity\ContactPhone;
use OroCRM\Bundle\ContactBundle\Entity\ContactAddress;
use OroCRM\Bundle\ContactBundle\Entity\ContactEmail;

/**
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * TODO Should be fixed during CRM-1185
 */
class ContactImportHelper
{
    /** @var array */
    protected $scalarFields = [
        'firstName',
        'lastName',
        'middleName',
        'namePrefix',
        'nameSuffix',
        'gender',
        'birthday'
    ];

    /** @var array */
    protected $addressScalarFields = [
        'label',
        'street',
        'street2',
        'city',
        'postalCode',
        'namePrefix',
        'middleName',
        'firstName',
        'lastName',
        'nameSuffix',
        'organization',
        'regionText'
    ];

    /** @var AddressImportHelper */
    protected $addressImportHelper;

    /** @var PropertyAccessor */
    protected $accessor;

    /** @var string */
    protected $priority;

    /**
     * @param Channel             $channel
     * @param AddressImportHelper $addressImportHelper
     */
    public function __construct(Channel $channel, AddressImportHelper $addressImportHelper)
    {
        $this->priority            = $channel->getSynchronizationSettings()->offsetGetOr('syncPriority');
        $this->addressImportHelper = $addressImportHelper;
        $this->accessor            = new PropertyAccessor();
    }

    /**
     * Merge contact information with remote customer data
     *
     * @param Customer $remoteData
     * @param Customer $localData
     * @param Contact  $contact
     */
    public function merge(Customer $remoteData, Customer $localData, Contact $contact)
    {
        $this->mergeScalars($this->scalarFields, $remoteData, $localData, $contact);
        $this->mergeObjects($remoteData, $localData, $contact);

        // contact entity must be valid
        if (!$contact->getFirstName()) {
            $contact->setFirstName('N/A');
        }
        if (!$contact->getLastName()) {
            $contact->setLastName('N/A');
        }
    }

    /**
     * Do merge of non-scalar fields such as emails or addresses
     *
     * @param Customer $remoteData
     * @param Customer $localData
     * @param Contact  $contact
     *
     * @SuppressWarnings(PHPMD)
     * TODO Should be fixed during CRM-1185
     */
    public function mergeObjects(Customer $remoteData, Customer $localData, Contact $contact)
    {
        // process emails
        $email = $contact->getPrimaryEmail();
        if ($email) {
            // if primary email exists try to merge
            $this->mergeScalars(['email'], $remoteData, $localData, $email);
        } elseif ($this->isRemotePrioritized()) {
            // if contact hasn't email and remote data has greater priority, then create it
            $email = new ContactEmail();
            $email->setPrimary(true);
            $email->setEmail($remoteData->getEmail());

            $contact->addEmail($email);
        }

        $addresses           = $contact->getAddresses();
        $isLocalTypesChanged = $this->isLocalAddressesTypesChanged($addresses, $localData);
        // loop through contact addresses form DB
        foreach ($addresses as $address) {
            // lookup for correspondent record in existing magento customer address list
            $localAddress = $this->getCustomerAddressByContactAddress($localData, $address);

            if (!$localAddress && $this->isRemotePrioritized()) {
                // case when magento local data does not have corresponded address and remote data has higher priority
                // override contact data then and remove this address
                $contact->removeAddress($address);
            } elseif ($localAddress) {
                $remoteAddress = $this->getCorrespondentRemoteAddress($remoteData, $localAddress);
                $contactPhone  = $localAddress->getContactPhone();
                if ($contactPhone) {
                    $contactPhone = $this->getContactPhoneFromContact($contact, $contactPhone);
                }

                if ($remoteAddress) {
                    // do update
                    $this->mergeScalars($this->addressScalarFields, $remoteAddress, $localAddress, $address);

                    if ($localAddress->getCountry()->getIso2Code() === $address->getCountry()->getIso2Code()
                        || $this->isRemotePrioritized()
                    ) {
                        $address->setCountry($remoteAddress->getCountry());
                    }

                    if ($this->isRegionChanged($remoteAddress, $address) || $this->isRemotePrioritized()) {
                        $address->setRegion($remoteAddress->getRegion());
                        if ($address->getRegion()) {
                            $address->setRegionText(null);
                        }
                    }

                    if ($this->isRemotePrioritized() || !$isLocalTypesChanged) {
                        $this->addressImportHelper->mergeAddressTypes($address, $remoteAddress);
                    }

                    if ($contactPhone) {
                        $this->mergeScalars(['phone'], $remoteAddress, $localAddress, $contactPhone);
                        if (!$contactPhone->getPhone()) {
                            $contact->removePhone($contactPhone);
                        }
                    } elseif (
                        $this->isRemotePrioritized()
                        && $remoteAddress->getPhone()
                        && $remoteAddress->getPhone() !== 'no phone'
                    ) {
                        $contactPhone = new ContactPhone();
                        $contactPhone->setPhone($remoteAddress->getPhone());
                        $contactPhone->setPrimary(!$contact->getPrimaryPhone());
                        $contact->addPhone($contactPhone);
                        $localAddress->setContactPhone($contactPhone);
                    }

                    $this->prepareAddress($address);
                    if (!$address->getCountry()) {
                        $contact->removeAddress($address);
                        if ($contactPhone) {
                            $contact->removePhone($contactPhone);
                        }
                    }
                } else {
                    $contact->removeAddress($address);
                    if ($contactPhone) {
                        $contact->removePhone($contactPhone);
                    }
                }
            }
        }

        /** @var ArrayCollection|Address[] $newAddresses */
        $newAddresses = $this->getOrphanRemoteAddresses($remoteData, $localData);
        foreach ($newAddresses as $address) {
            /*
             * Will create new address if remote data has higher priority and means
             * that address removed from contact and remove should be cancelled.
             * Another case if it's newly created address, then process it anyway
             */
            if ($this->isRemotePrioritized() || !$address->getId()) {
                $contactAddress = new ContactAddress();

                $this->mergeScalars($this->addressScalarFields, $address, $contactAddress, $contactAddress);
                $contactAddress->setCountry($address->getCountry());
                $contactAddress->setRegion($address->getRegion());
                $contactAddress->setTypes($address->getTypes());

                $this->prepareAddress($contactAddress);
                if ($contactAddress->getCountry()) {
                    $contact->addAddress($contactAddress);
                    $address->setContactAddress($contactAddress);
                }
                if ($address->getContactPhone()) {
                    $address->getContactPhone()->setOwner($contact);
                }
            }
        }

        /** @var ContactAddress $toBePrimary */
        $toBePrimary = $contact->getAddresses()->first();
        if (!$contact->getPrimaryAddress() && $toBePrimary) {
            $toBePrimary->setPrimary(true);
        }

        // Set contact primary phone if none
        if (!$contact->getPrimaryPhone()) {
            if ($contact->getPhones()->count() > 0) {
                $contact->getPhones()->first()->setPrimary(true);
            }
        }
    }

    /**
     * @param ContactAddress $contactAddress
     */
    public function prepareAddress(ContactAddress $contactAddress)
    {
        // at this point imported address region have code equal to region_id in magento db field
        $mageRegionId = $contactAddress->getRegion() ? $contactAddress->getRegion()->getCode() : null;
        $contactAddress->setId(null);

        $this->addressImportHelper->updateAddressCountryRegion($contactAddress, $mageRegionId);
        $this->addressImportHelper->updateAddressTypes($contactAddress);
    }

    /**
     * @param Address        $remoteAddress
     * @param ContactAddress $address
     *
     * @return bool
     */
    protected function isRegionChanged($remoteAddress, $address)
    {
        return (
            ($remoteAddress->getRegion() == $address->getRegion())
            ||
            (
                ($remoteAddress->getRegion() ? $remoteAddress->getRegion()->getCombinedCode() : null)
                === ($address->getRegion() ? $address->getRegion()->getCombinedCode() : null)
            )
        );
    }

    /**
     * Get ContactPhone from contact by ContactPhone
     *
     * @param Contact      $contact
     * @param ContactPhone $contactPhone
     *
     * @return mixed
     */
    protected function getContactPhoneFromContact(Contact $contact, ContactPhone $contactPhone)
    {
        $filtered = $contact->getPhones()->filter(
            function (ContactPhone $phone) use ($contactPhone) {
                return $phone && $phone->getId() === $contactPhone->getId();
            }
        );

        return $filtered->first();
    }

    /**
     * Do merge between remote data and local data relation
     * If field changed on both sides check priority
     *
     * @param array  $fieldsList List of scalar fields to merge
     * @param object $remoteData Data from remote instance
     * @param object $localData  Current database data
     * @param object $mergedData Data needs to merge
     */
    protected function mergeScalars(array $fieldsList, $remoteData, $localData, $mergedData)
    {
        foreach ($fieldsList as $field) {
            if (!$this->isFieldChanged($field, $localData, $mergedData) || $this->isRemotePrioritized()) {
                // override always except when field is changed and local data has greater priority
                $this->accessor->setValue($mergedData, $field, $this->accessor->getValue($remoteData, $field));
            }
        }
    }

    /**
     * Check whatever remote data configured to have greater priority
     *
     * @return bool
     */
    protected function isRemotePrioritized()
    {
        return $this->priority === TwoWaySyncConnectorInterface::REMOTE_WINS;
    }

    /**
     * Check whenever field is changed
     *
     * @param string $field
     * @param object $baseObject
     * @param object $inheritedObject
     *
     * @return bool
     */
    protected function isFieldChanged($field, $baseObject, $inheritedObject)
    {
        $oldValue = $this->accessor->getValue($baseObject, $field);
        $newValue = $this->accessor->getValue($inheritedObject, $field);

        return $oldValue !== $newValue;
    }

    /**
     * Find customer address by given contact address
     *
     * @param Customer       $customer
     * @param ContactAddress $contactAddress
     *
     * @return Address|false
     */
    protected function getCustomerAddressByContactAddress(Customer $customer, ContactAddress $contactAddress)
    {
        $filtered = $customer->getAddresses()->filter(
            function (Address $address) use ($contactAddress) {
                return $address->getContactAddress()
                && $address->getContactAddress()->getId() === $contactAddress->getId();
            }
        );

        return $filtered->first();
    }

    /**
     * Find correspondent to remote address by local one
     *
     * @param Customer $customer
     * @param Address  $localAddress
     *
     * @return Address|false
     */
    protected function getCorrespondentRemoteAddress(Customer $customer, Address $localAddress)
    {
        $filtered = $customer->getAddresses()->filter(
            function (Address $address) use ($localAddress) {
                return $address->getOriginId() === $localAddress->getOriginId();
            }
        );

        return $filtered->first();
    }

    /**
     * Find correspondent to local address by remote one
     *
     * @param Customer $customer
     * @param Address  $remoteAddress
     *
     * @return Address|false
     */
    protected function getCorrespondentLocalAddress(Customer $customer, Address $remoteAddress)
    {
        $filtered = $customer->getAddresses()->filter(
            function (Address $address) use ($remoteAddress) {
                return $remoteAddress->getOriginId() === $address->getOriginId();
            }
        );

        return $filtered->first();
    }

    /**
     * Find remote addresses that do not have correspondent one in database
     *
     * @param Customer $remoteCustomer
     * @param Customer $localCustomer
     *
     * @return ArrayCollection
     */
    protected function getOrphanRemoteAddresses(Customer $remoteCustomer, Customer $localCustomer)
    {
        $self = $this;

        $filtered = $remoteCustomer->getAddresses()->filter(
            function (Address $address) use ($self, $localCustomer) {
                return !$self->getCorrespondentLocalAddress($localCustomer, $address);
            }
        );

        return $filtered;
    }

    /**
     * Checks whenever if any of contact address types was modified since last sync from magento
     *
     * @param AbstractAddress[] $contactAddresses
     * @param Customer          $localCustomer
     *
     * @return bool
     */
    protected function isLocalAddressesTypesChanged($contactAddresses, Customer $localCustomer)
    {
        foreach ($contactAddresses as $contactAddress) {
            $localAddress = $this->getCustomerAddressByContactAddress($localCustomer, $contactAddress);
            if ($localAddress) {
                $contactAddressTypes = $contactAddress->getTypeNames();
                $localTypes          = $localAddress->getTypeNames();

                $typesDiff = array_diff($contactAddressTypes, $localTypes);
                if (!empty($typesDiff)) {
                    return true;
                }
            }
        }

        return false;
    }
}
