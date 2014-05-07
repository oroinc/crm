<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Strategy\MergeHelper;

use OroCRM\Bundle\ContactBundle\Entity\ContactAddress;
use OroCRM\Bundle\MagentoBundle\Entity\Address;
use Symfony\Component\PropertyAccess\PropertyAccessor;

use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\IntegrationBundle\Form\Type\ChannelType;

use OroCRM\Bundle\ContactBundle\Entity\Contact;
use OroCRM\Bundle\ContactBundle\Entity\ContactEmail;
use OroCRM\Bundle\MagentoBundle\Entity\Customer;

class ContactMergeHelper
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

    /** @var PropertyAccessor */
    protected $accessor;

    /** @var string */
    protected $priority;

    public function __construct(Channel $channel)
    {
        $this->priority = $channel->getSyncPriority();
        $this->accessor = new PropertyAccessor();
    }

    public function merge(Customer $remoteData, Customer $localData, Contact $contact)
    {
        $this->mergeScalars($this->scalarFields, $remoteData, $localData, $contact);
        $this->mergeObjects($remoteData, $localData, $contact);
    }

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

        // process addresses
        /*
        $addresses = $contact->getAddresses();
        foreach ($addresses as $address) {
            // find in update local data if
            if (!$this->getCustomerAddressByContactAddress($localData, $address) && $this->isRemotePrioritized()) {
                // remove if added and
                $contact->removeAddress($address);
            } else {
                // do update
            }
        }*/
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
        return $this->priority === ChannelType::REMOTE_WINS;
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
     * @return Address|null
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
}
