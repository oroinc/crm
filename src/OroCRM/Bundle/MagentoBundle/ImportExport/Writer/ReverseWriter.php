<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Writer;

use Doctrine\ORM\EntityManager;

use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

use Akeneo\Bundle\BatchBundle\Item\ItemWriterInterface;

use Oro\Bundle\IntegrationBundle\Form\EventListener\ChannelFormTwoWaySyncSubscriber;
use Oro\Bundle\AddressBundle\ImportExport\Serializer\Normalizer\AddressNormalizer;

use OroCRM\Bundle\MagentoBundle\Entity\Customer;
use OroCRM\Bundle\MagentoBundle\Provider\Transport\SoapTransport;

use OroCRM\Bundle\MagentoBundle\ImportExport\Strategy\StrategyHelper\AddressImportHelper;

use OroCRM\Bundle\MagentoBundle\ImportExport\Serializer\CustomerSerializer;
use OroCRM\Bundle\MagentoBundle\ImportExport\Processor\AbstractReverseProcessor;

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

    /** @var EntityManager */
    protected $em;

    /** @var CustomerSerializer */
    protected $customerSerializer;

    /** @var AddressNormalizer */
    protected $addressNormalizer;

    /** @var SoapTransport */
    protected $transport;

    /** @var PropertyAccessor */
    protected $accessor;

    /** @var AddressImportHelper */
    protected $addressImportHelper;

    /**
     * @param EntityManager      $em
     * @param CustomerSerializer $customerSerializer
     * @param AddressNormalizer  $addressNormalizer
     * @param SoapTransport      $transport
     * @param AddressImportHelper $addressImportHelper
     */
    public function __construct(
        EntityManager $em,
        CustomerSerializer $customerSerializer,
        AddressNormalizer $addressNormalizer,
        SoapTransport $transport,
        AddressImportHelper $addressImportHelper
    ) {
        $this->em                  = $em;
        $this->customerSerializer  = $customerSerializer;
        $this->addressNormalizer   = $addressNormalizer;
        $this->transport           = $transport;
        $this->accessor            = PropertyAccess::createPropertyAccessor();
        $this->addressImportHelper = $addressImportHelper;
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
                        $customerForMagento = $this->customerSerializer->normalize($customer);
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
                        $this->processAddresses(
                            $item->object['addresses'],
                            $channel->getSyncPriority(),
                            $customer
                        );
                    }

                } catch (\Exception $e) {
                    //process another entity even in case if exception thrown
                    continue;
                }
            }
        }
        $this->em->flush();
    }

    /**
     * Process address write  to remote instance and to DB
     *
     * @param array    $addresses
     * @param string   $syncPriority
     * @param Customer $customer
     */
    protected function processAddresses($addresses, $syncPriority, Customer $customer)
    {
        foreach ($addresses as $address) {
            if (isset($address['status']) && $address['status'] === AbstractReverseProcessor::UPDATE_ENTITY) {
                $addressEntity = $address['entity'];
                $localChanges  = $address['object'];

                if ($syncPriority === ChannelFormTwoWaySyncSubscriber::REMOTE_WINS) {
                    $remoteData = $this->customerSerializer->compareAddresses(
                        (array)$this->transport->call(
                            SoapTransport::ACTION_CUSTOMER_ADDRESS_INFO,
                            [
                            'addressId' => $addressEntity->getOriginId()
                            ]
                        ),
                        $addressEntity,
                        array_keys($localChanges)
                    );
                    $this->setChangedData($addressEntity, $localChanges);
                    $this->setRemoteDataChanges($addressEntity, $remoteData);
                } else {
                    $this->setChangedData($addressEntity, $localChanges);
                }

                $this->updateRemoteAddressData(
                    $addressEntity->getOriginId(),
                    $this->addressNormalizer->normalize($addressEntity)
                );
                $this->em->persist($addressEntity);
                unset($addressEntity, $localChanges, $remoteData);
            }
            if (isset($address['status']) && $address['status'] === AbstractReverseProcessor::DELETE_ENTITY) {
                $result = null;
                try {
                    $result = $this->transport->call(
                        SoapTransport::ACTION_CUSTOMER_ADDRESS_DELETE,
                        [
                        'addressId' => $address['entity']->getOriginId()
                        ]
                    );
                } catch (\Exception $e) {
                    $this->em->remove($address['entity']);
                }

                if ($result) {
                    $this->em->remove($address['entity']);
                }
                $this->em->flush();
                unset($result);
            }
            if (isset($address['status']) && $address['status'] === AbstractReverseProcessor::NEW_ENTITY) {
                try {
                    if ($syncPriority === ChannelFormTwoWaySyncSubscriber::REMOTE_WINS) {

                        $dataForSend = $this->customerSerializer->convertToMagentoAddress($address['entity']);
                        $requestData = array_merge(
                            ['customerId' => $address['magentoId']],
                            [
                                'addressData' => array_merge(
                                    $dataForSend,
                                    ['telephone' => 'no phone']
                                )
                            ]
                        );

                        $result = $this->transport->call(
                            SoapTransport::ACTION_CUSTOMER_ADDRESS_CREATE,
                            $requestData
                        );

                        if ($result) {
                            $newAddress = $this->customerSerializer
                                ->convertMageAddressToAddress($dataForSend, $address['entity'], $result);
                            $newAddress->setOwner($customer);
                            $customer->addAddress($newAddress);
                            $this->em->persist($customer);
                            $this->em->flush();
                        }
                    }
                } catch (\Exception $e) {
                }
                unset($result, $requestData);
            }
        }
    }

    /**
     * Push data to remote instance
     *
     * @param int   $addressId
     * @param array $addressData
     */
    protected function updateRemoteAddressData($addressId, $addressData)
    {
        foreach ($addressData as $fieldName => $value) {
            if ($value instanceof \DateTime) {
                /** @var $value \DateTime */
                $addressData[$fieldName] = $value->format(self::MAGENTO_DATETIME_FORMAT);
            }
        }
        $addressData = $this->customerSerializer->convertToMagentoAddress($addressData);

        $requestData = array_merge(
            ['addressId' => $addressId],
            ['addressData' => $addressData]
        );
        $this->transport->call(SoapTransport::ACTION_CUSTOMER_ADDRESS_UPDATE, $requestData);
    }

    /**
     * Push data to remote instance
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
     * @param \stdClass $item
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
     * @param \OroCRM\Bundle\MagentoBundle\Entity\Address $entity
     * @param array $changedData
     */
    protected function setRemoteDataChanges($entity, array $changedData)
    {
        foreach ($changedData as $fieldName => $value) {
            if ($fieldName !== 'addresses') {
                if ($fieldName === 'region') {
                    try {
                        $code = $this->accessor->getValue($value, 'code');
                        $this->addressImportHelper->updateAddressCountryRegion($entity, $code);
                    } catch (\Exception $e) {
                    }

                } else {
                    $this->accessor->setValue($entity, $fieldName, $value);
                }

            }
        }
    }

    /**
     * Convert email to sting
     *
     * @param mixed $email
     *
     * @return string|null
     */
    protected function emailParser($email)
    {
        if (is_object($email)) {
            try {
                return (string)$email;
            } catch (\Exception $e) {
                $email = null;
            }
        }

        return $email;
    }

    /**
     * Check if magento extension not installed and fix data set
     * In the magento version up to 1.8.0.0 we can send only: email, firstname, lastname.
     *
     * @param \stdClass $remoteData
     */
    protected function fixDataIfExtensionNotInstalled(\stdClass $remoteData)
    {
        if (!$this->transport->isExtensionInstalled()) {
            foreach ($remoteData as $key => $value) {
                if (!in_array($key, $this->clearMagentoFields)) {
                    unset($remoteData->$key);
                }
            }
        }
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

        $contact = $this->accessor->getValue($item->entity, 'contact');
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
