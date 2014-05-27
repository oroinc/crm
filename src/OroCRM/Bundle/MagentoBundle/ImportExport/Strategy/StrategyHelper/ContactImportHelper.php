<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Strategy\StrategyHelper;

use Doctrine\Common\Collections\ArrayCollection;

use Symfony\Component\PropertyAccess\PropertyAccessor;

use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\IntegrationBundle\Form\EventListener\ChannelFormTwoWaySyncSubscriber;

use OroCRM\Bundle\MagentoBundle\Entity\Address;
use OroCRM\Bundle\MagentoBundle\Entity\Customer;
use OroCRM\Bundle\ContactBundle\Entity\Contact;
use OroCRM\Bundle\ContactBundle\Entity\ContactAddress;
use OroCRM\Bundle\ContactBundle\Entity\ContactEmail;

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
        $this->priority            = $channel->getSyncPriority();
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
    }

    /**
     * Do merge of non-scalar fields such as emails or addresses
     *
     * @param Customer $remoteData
     * @param Customer $localData
     * @param Contact  $contact
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
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

        // @TODO process statuses
        // process addresses
        $addresses = $contact->getAddresses();
        foreach ($addresses as $address) {
            // find in update local data if
            $localAddress = $this->getCustomerAddressByContactAddress($localData, $address);

            if (!$localAddress && $this->isRemotePrioritized()) {
                 $contact->removeAddress($address);
            } elseif ($localAddress) {
                 $remoteAddress = $this->getCorrespondentRemoteAddress($remoteData, $localAddress);

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
                    $address->setTypes($remoteAddress->getTypes());
                    $this->prepareAddress($address);
                    if (!$address->getCountry()) {
                        $contact->removeAddress($address);
                    }
                } else {
                     $contact->removeAddress($address);
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
        return $this->priority === ChannelFormTwoWaySyncSubscriber::REMOTE_WINS;
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
     * @return \Doctrine\Common\Collections\Collection
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
}
