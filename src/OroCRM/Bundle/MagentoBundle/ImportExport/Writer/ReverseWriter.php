<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Writer;

use Doctrine\ORM\EntityManager;

use OroCRM\Bundle\MagentoBundle\ImportExport\Processor\AbstractReverseProcessor;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

use Akeneo\Bundle\BatchBundle\Item\ItemWriterInterface;

use Oro\Bundle\IntegrationBundle\Form\EventListener\ChannelFormTwoWaySyncSubscriber;

use OroCRM\Bundle\MagentoBundle\Entity\Customer;
use OroCRM\Bundle\MagentoBundle\ImportExport\Serializer\CustomerSerializer;
use OroCRM\Bundle\MagentoBundle\Provider\Transport\SoapTransport;

class ReverseWriter implements ItemWriterInterface
{
    const MAGENTO_DATETIME_FORMAT = 'Y-m-d H:i:s';

    /**
     * If at remote side where id no OroBridge installed, will be processed only this fields
     *
     * @var array
     */
    protected $clearMagentoFields = [
        'email',
        'firstname',
        'lastname'
    ];

    /**
     * Customer-Contact relation, key - Customer field, value - Contact field
     *
     * @var array
     */
    protected $customerContactRelation = [
        'name_prefix' => 'name_prefix',
        'first_name'  => 'first_name',
        'middle_name' => 'middle_name',
        'last_name'   => 'last_name',
        'name_suffix' => 'name_suffix',
        'gender'      => 'gender',
        'birthday'    => 'birthday',
        'email'       => 'primary_email.email',
    ];

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var CustomerSerializer
     */
    protected $customerSerializer;

    /**
     * @var SoapTransport
     */
    protected $transport;

    /**
     * @var PropertyAccessor
     */
    protected $accessor;

    /**
     * @param EntityManager            $em
     * @param CustomerSerializer       $customerSerializer
     * @param SoapTransport            $transport
     */
    public function __construct(
        EntityManager $em,
        CustomerSerializer $customerSerializer,
        SoapTransport $transport
    ) {
        $this->em                 = $em;
        $this->customerSerializer = $customerSerializer;
        $this->transport          = $transport;
        $this->accessor           = PropertyAccess::createPropertyAccessor();
    }

    /**
     * {@inheritDoc}
     */
    public function write(array $items)
    {
        foreach ($items as $item) {
            if (!empty($item->object)) {
                try {
                    /** @var Customer $customer */
                    $customer = $item->entity;
                    $channel  = $customer->getChannel();
                    if (!empty($item->object['email'])) {
                        $item->object['email'] = $this->emailParser($item->object['email']);
                    }
                    $this->transport->init($channel->getTransport($customer));
                    $localUpdatedData = $this->customerSerializer->normalize($item->entity, null, $item->object);

                    // REMOTE WINS
                    if ($channel->getSyncPriority() === ChannelFormTwoWaySyncSubscriber::REMOTE_WINS) {
                        $remoteChanges = $this->getCustomerRemoteChangeSet(
                            $item,
                            array_keys($localUpdatedData)
                        );
                        $this->setChangedData($customer, $item->object);
                        $this->setChangedData($customer, $remoteChanges);
                        $this->em->persist($customer);
                        $customerForMagento = $this->customerSerializer->normalize($customer, $this->accessor);
                        $this->updateRemoteData($customer->getOriginId(), $customerForMagento);
                        $this->updateContact($item);

                    } elseif ($channel->getSyncPriority() === ChannelFormTwoWaySyncSubscriber::LOCAL_WINS) {
                        // local wins
                        $this->updateRemoteData($customer->getOriginId(), $localUpdatedData);
                        $this->setChangedData($customer, $item->object);
                        $this->em->persist($customer);
                    }

                    // process addresses
                    if (isset($item->object['addresses'])) {
                        $this->processAddresses($item->object['addresses'], $channel->getSyncPriority());
                    }

                } catch (\Exception $e) {
                    //process another entity even in case if exception thrown
                    continue;
                }
            }
        }
        $this->em->flush();
    }

    protected function processAddresses($addresses, $syncPriority)
    {
        foreach ($addresses as $address) {
            if ($address->status === AbstractReverseProcessor::UPDATE_ENTITY) {
                if ($syncPriority === ChannelFormTwoWaySyncSubscriber::REMOTE_WINS) {

                } else {
                    $addressData = $this->customerSerializer->normalizeAddress($address);
                    $requestData = array_merge(
                        ['addressId' => $address->entity->getOriginId()],
                        ['addressData' => $addressData]
                    );
                    $this->transport->call(SoapTransport::ACTION_CUSTOMER_ADDRESS_UPDATE, $requestData);
                    $this->setChangedData($address->entity, $address->object);
                    $this->em->persist($address->entity);
                }
            }
        }
    }

    /**
     * Update customer data at remote side
     *
     * @param int   $customerId
     * @param array $customerData
     */
    protected function updateRemoteData($customerId, $customerData)
    {
        foreach ($customerData as $fieldName => $value) {
            if ($value instanceof \DateTime) {
                /** @var $value \DateTime */
                $customerData[$fieldName] = $value->format(self::MAGENTO_DATETIME_FORMAT);
            }
        }
        $requestData = array_merge(
            ['customerId' => $customerId],
            ['customerData' => $customerData]
        );
        $this->transport->call(SoapTransport::ACTION_CUSTOMER_UPDATE, $requestData);
    }

    /**
     * Get changes from magento side
     *
     * @param       $item
     * @param array $fieldsList
     *
     * @return array
     */
    protected function getCustomerRemoteChangeSet($item, $fieldsList)
    {
        $remoteData = $this->transport->call(
            SoapTransport::ACTION_CUSTOMER_INFO,
            [
                'customerId' => $item->entity->getOriginId(),
                'attributes' => $fieldsList
            ]
        );

        unset($remoteData->customer_id);

        /** cut data */
        $this->fixDataIfExtensionNotInstalled($remoteData);
        $customerLocalData = $this->customerSerializer->getCurrentCustomerValues(
            $item->entity,
            $fieldsList
        );
        foreach ($remoteData as $fieldName => $value) {
            if (isset($customerLocalData[$fieldName]) && $customerLocalData[$fieldName] === $value) {
                unset ($remoteData->{$fieldName});
            }
        }

        return (array)$remoteData;
    }

    /**
     * Set changed data to customer
     *
     * @param Customer $entity
     * @param array    $changedData
     */
    protected function setChangedData($entity, array $changedData)
    {
        foreach ($changedData as $fieldName => $value) {
            if ($fieldName !== 'addresses') {
                $this->accessor->setValue($entity, $fieldName, $value);
            }
        }
    }

    /**
     * @param object|string $email
     *
     * @return string
     */
    protected function emailParser($email)
    {
        if (is_object($email)) {
            try {
                return (string)$email;
            } catch (\Exception $e) {

            }
        }

        return $email;
    }

    /**
     * Check if magento extension not installed and fix data set
     * In the magento version 1.8.0.0 we can send only these fields: email, firstname, lastname.
     *
     * @param \stdClass $remoteData
     */
    protected function fixDataIfExtensionNotInstalled(\stdClass $remoteData)
    {
        // todo: Uncomment this check after oro bridge was fixed to support all fields
        #if (!$this->transport->isExtensionInstalled()) {
        foreach ($remoteData as $key => $value) {
            if (!in_array($key, $this->clearMagentoFields)) {
                unset($remoteData->$key);
            }
        }
        #}
    }

    /**
     * Update contact data
     *
     * @param $item
     */
    protected function updateContact($item)
    {
        $contactData = [];
        foreach ($this->customerContactRelation as $customerField => $contactField) {
            $contactData[$contactField] = $this->accessor->getValue($item->entity, $customerField);
        }

        $contact     = $this->accessor->getValue($item->entity, 'contact');

        foreach ($contactData as $fieldName => $value) {
            try {
                $this->accessor->setValue($contact, $fieldName, $value);
            } catch (\Exception $e) {
                /**
                 * @todo: if email is null? need to set primary email (create)
                 */
            }
        }
        $this->em->persist($contact);
    }
}
