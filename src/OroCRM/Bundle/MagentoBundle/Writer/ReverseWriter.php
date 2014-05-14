<?php

namespace OroCRM\Bundle\MagentoBundle\Writer;

use Akeneo\Bundle\BatchBundle\Item\ItemWriterInterface;
use Doctrine\ORM\EntityManager;
use Oro\Bundle\IntegrationBundle\Form\EventListener\ChannelFormTwoWaySyncSubscriber;
use OroCRM\Bundle\MagentoBundle\Entity\Customer;
use OroCRM\Bundle\MagentoBundle\ImportExport\Serializer\CustomerDenormalizer;
use OroCRM\Bundle\MagentoBundle\Provider\Transport\SoapTransport;
use Oro\Bundle\IntegrationBundle\Provider\ConnectorContextMediator;
use Symfony\Component\PropertyAccess\PropertyAccess;

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


    public function __construct(EntityManager $em, CustomerDenormalizer $customerSerializer, ConnectorContextMediator $helper, SoapTransport $transport) {
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
                    $this->transport->init($channel->getTransport($customer));
                    $localUpdatedData = $this->customerSerializer->normalize($item);
                    if ($channel->getSyncPriority() === ChannelFormTwoWaySyncSubscriber::REMOTE_WINS) {
                        $remoteData = $this->transport->call(
                            SoapTransport::ACTION_CUSTOMER_INFO,
                            [
                                'customerId' => $customer->getOriginId(),
                                'attributes' => array_keys($localUpdatedData)
                            ]
                        );

                        $customerChangeset = $this->customerSerializer->getCurrentCustomerValues($customer, array_keys($remoteData), $this->accessor);
                    }

                    $requestData = array_merge(
                        ['customerId' => $customer->getOriginId()],
                        ['customerData' => $localUpdatedData]
                    );

                    if ($this->transport->call(SoapTransport::ACTION_CUSTOMER_UPDATE, $requestData)) {
                        $this->updateLocal($item);
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

    protected function updateLocal($item)
    {
        $entity = $item->entity;
        foreach ($item->object as $fieldName => $value) {
            if (!is_object($value)) {
                $this->accessor->setValue($entity, $fieldName, $value);
            }
        }

        $this->em->persist($entity);
    }
}
