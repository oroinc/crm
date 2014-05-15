<?php

namespace OroCRM\Bundle\MagentoBundle\Writer;

use Doctrine\ORM\EntityManager;

use Symfony\Component\PropertyAccess\PropertyAccess;

use Akeneo\Bundle\BatchBundle\Item\ItemWriterInterface;

use Oro\Bundle\IntegrationBundle\Form\EventListener\ChannelFormTwoWaySyncSubscriber;
use OroCRM\Bundle\MagentoBundle\Entity\Customer;
use OroCRM\Bundle\MagentoBundle\ImportExport\Serializer\CustomerDenormalizer;
use OroCRM\Bundle\MagentoBundle\Provider\Transport\SoapTransport;
use Oro\Bundle\IntegrationBundle\Provider\ConnectorContextMediator;

class ReverseWriter implements ItemWriterInterface
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var CustomerDenormalizer
     */
    protected $customerSerializer;

    /** @var ConnectorContextMediator */
    protected $helper;

    /**
     * @var SoapTransport
     */
    protected $transport;

    /**
     * @var PropertyAccess
     */
    protected $accessor;

    /**
     * @param EntityManager            $em
     * @param CustomerDenormalizer     $customerSerializer
     * @param ConnectorContextMediator $helper
     * @param SoapTransport            $transport
     */
    public function __construct(
        EntityManager $em,
        CustomerDenormalizer $customerSerializer,
        ConnectorContextMediator $helper,
        SoapTransport $transport
    ) {
        $this->em = $em;
        $this->customerSerializer = $customerSerializer;
        $this->helper = $helper;
        $this->transport = $transport;
        $this->accessor = PropertyAccess::createPropertyAccessor();
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
                    $channel = $customer->getChannel();

                    if (!empty($item->object['email'])) {
                        $item->object['email'] = $this->emailParser($item->object['email']);
                    }

                    $this->transport->init($channel->getTransport($customer));

                    $localUpdatedData = $this->customerSerializer
                        ->normalize($item->entity, null, $item->object);

                    /**
                     * REMOTE WINS
                     */
                    if ($channel->getSyncPriority() === ChannelFormTwoWaySyncSubscriber::REMOTE_WINS) {

                        $remoteData = $this->transport->call(
                            SoapTransport::ACTION_CUSTOMER_INFO,
                            [
                                'customerId' => $customer->getOriginId(),
                                'attributes' => array_keys($localUpdatedData)
                            ]
                        );

                        /** cut data */
                        $this->fixDataIfExtensionNotInstalled($remoteData);

                        $customerLocalChangeSet = $this->customerSerializer
                            ->getCurrentCustomerValues($item->entity, $remoteData, $this->accessor);

                        $customerRemoteChangeSet = $this->customerSerializer
                            ->convertToOroStyle($remoteData);

                        /**
                         * @todo: make change set,
                         * find delta between $customerLocalChangeSet and $customerRemoteChangeSet
                         */
                        $customerChangeSet = $customerRemoteChangeSet;


                        $this->updateFromRemote($item, $customerChangeSet);

                        $customerForMagento = $this->customerSerializer
                            ->normalize($item->entity, $this->accessor);

                        $requestData = array_merge(
                            ['customerId' => $customer->getOriginId()],
                            ['customerData' => $customerForMagento]
                        );

                        $soapResult = $this->transport
                            ->call(SoapTransport::ACTION_CUSTOMER_UPDATE, $requestData);

                        /**
                         * @todo: here will be contact update
                         */
                        $this->updateContact($item);

                    } elseif ($channel->getSyncPriority() === ChannelFormTwoWaySyncSubscriber::LOCAL_WINS) {
                        $requestData = array_merge(
                            ['customerId' => $customer->getOriginId()],
                            ['customerData' => $localUpdatedData]
                        );
                        $soapResult = $this->transport->call(SoapTransport::ACTION_CUSTOMER_UPDATE, $requestData);

                        if ($soapResult) {
                            $this->updateFromLocal($item);
                        }
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
     * @param Object $item
     *
     * @return Object
     */
    protected function getTransport($item)
    {
        return $item->getChannel()->getTransport();
    }

    protected function updateFromLocal($item)
    {
        $entity = $item->entity;

        $this->setChangedData($entity, $item->object);

        $this->em->persist($entity);
    }

    protected function setChangedData($entity, array $fields)
    {
        foreach ($fields as $fieldName => $value) {
            if (!is_object($value) && !is_array($value)) {
                $this->accessor->setValue($entity, $fieldName, $value);
            }
        }
    }

    protected function updateFromRemote($item, $remoteData)
    {
        $entity = $item->entity;
        /** set local */
        $this->setChangedData($entity, $item->object);
        /** set remote */
        $this->setChangedData($entity, $remoteData);

        $this->em->persist($entity);
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
     * !!!BECAUSE in the magento version 1.8.0.0 we can send only these fields: email, firstname, lastname
     *
     * @param \stdClass $remoteData
     */
    protected function fixDataIfExtensionNotInstalled(\stdClass $remoteData)
    {
        $filter = ['customer_id', 'email', 'firstname', 'lastname'];

        #if (!$this->transport->isExtensionInstalled()) {
        foreach ($remoteData as $key => $value) {
            if (!in_array($key, $filter)) {
                unset($remoteData->$key);
            }
        }
        #}
    }

    protected function updateContact($item)
    {
        $contactData = $this->customerSerializer
            ->convertToDataForContact($item->entity, $this->accessor);
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
