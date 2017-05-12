<?php

namespace OroCRM\Bundle\MagentoBundle\Manager;

use Doctrine\ORM\EntityManager;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

use Oro\Bundle\ImportExportBundle\Context\Context;
use Oro\Bundle\IntegrationBundle\Entity\Channel;

use OroCRM\Bundle\ContactBundle\Entity\Contact;
use OroCRM\Bundle\ContactBundle\Entity\ContactAddress;
use OroCRM\Bundle\MagentoBundle\Entity\Address;
use OroCRM\Bundle\MagentoBundle\Entity\Customer;
use OroCRM\Bundle\MagentoBundle\Exception\ManyAddressesException;
use OroCRM\Bundle\MagentoBundle\ImportExport\Converter\CustomerAddressDataConverter;
use OroCRM\Bundle\MagentoBundle\ImportExport\Processor\ContextProcessor;
use OroCRM\Bundle\MagentoBundle\ImportExport\Strategy\CustomerAddressStrategy;
use OroCRM\Bundle\MagentoBundle\Manager\CustomerAddress\ConvertAddressToContactAdress;
use OroCRM\Bundle\MagentoBundle\Provider\Transport\MagentoTransportInterface;

class CustomerInfoManager implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /** @var EntityManager */
    protected $em;

    /** @var PropertyAccessor  */
    protected $accessor;

    /** @var MagentoTransportInterface */
    protected $transport;

    /** @var CustomerAddressDataConverter */
    protected $customerAddressDataConverter;

    /** @var  CustomerAddressStrategy */
    protected $customerAddressStrategy;

    /** @var  ContextProcessor */
    protected $contextProcessor;

    /** @var ConvertAddressToContactAdress */
    protected $convertAddressToContactAdress;

    protected $baseAddressProperties = [
        'label',
        'street',
        'street2',
        'city',
        'postalCode',
        'country',
        'organization',
        'region',
        'regionText',
        'namePrefix',
        'firstName',
        'middleName',
        'lastName',
        'nameSuffix',
        'types'
    ];

    /**
     * @param EntityManager $em
     * @param MagentoTransportInterface $transport
     * @param CustomerAddressDataConverter $customerAddressDataConverter
     * @param CustomerAddressStrategy $customerAddressStrategy
     * @param ContextProcessor $contextProcessor
     * @param ConvertAddressToContactAdress $convertAddressToContactAdress
     */
    public function __construct(
        EntityManager $em,
        MagentoTransportInterface $transport,
        CustomerAddressDataConverter $customerAddressDataConverter,
        CustomerAddressStrategy $customerAddressStrategy,
        ContextProcessor $contextProcessor,
        ConvertAddressToContactAdress $convertAddressToContactAdress
    ) {
        $this->em = $em;
        $this->accessor = PropertyAccess::createPropertyAccessor();
        $this->transport = $transport;
        $this->customerAddressDataConverter = $customerAddressDataConverter;
        $this->customerAddressStrategy = $customerAddressStrategy;
        $this->contextProcessor = $contextProcessor;
        $this->convertAddressToContactAdress = $convertAddressToContactAdress;
    }

    /**
     * @param int[] $integrationIds
     * @param int[]|null $customersIds
     * @param int $batchSize
     */
    public function reSyncData($integrationId, $customersIds = null, $batchSize = 25)
    {
        $i = 0;
        $this->logger->info('Start process');
        $iterator = $this->getCustomerIterator($customersIds, $integrationId);
        $repositoryIntegrationChannel = $this->em->getRepository('OroIntegrationBundle:Channel');
        $integration = $repositoryIntegrationChannel->find($integrationId);

        $this->transport->init($integration->getTransport());
        $iterator->setBufferSize($batchSize);
        $customerCount = $iterator->count();
        $iterator->setPageCallback(function () use (&$i, $customerCount) {
            $this->em->flush();
            $this->logger->info(sprintf('Processed %s customers from %s', $i, $customerCount));
        });

        /** @var Customer $customer */
        foreach ($iterator as $customer) {
            $i++;
            if ($customer->getOriginId()) {
                $magentoCustomerAddresses = $this->transport->getCustomerAddresses($customer->getOriginId());
                $contact = $customer->getContact();

                foreach ($magentoCustomerAddresses as $data) {
                    try {
                        /** @var Address $address */
                        $address = $this->convertDataIntoAddress($data, $integration);
                        $address->setOwner($customer);
                        if ($customer->getAddresses()->count() === 0) {
                            $address->setPrimary(true);
                            $customer->addAddress($address);
                        }

                        $customerAddress = $this->findCustomerAddress($customer, $address->getOriginId());

                        if ($customerAddress) {
                            $address = $this->updateAddressByDataFromMagento($customerAddress, $address);

                            if ($contact) {
                                $contactAddress = $this->handleContactAddress($customerAddress, $address, $contact);
                                $address->setContactAddress($contactAddress);
                                $this->em->persist($contactAddress);
                            }
                        } else {
                            if ($contact) {
                                $contactAddress = $this->convertAddressToContactAdress->convert($address);
                                $contactAddress->setOwner($contact);

                                $address->setContactAddress($contactAddress);
                                $address->setContactPhone($customer->getContact()->getPrimaryPhone());
                                $this->em->persist($contactAddress);
                            }
                        }

                        $this->em->persist($address);
                    } catch (ManyAddressesException $e) {
                        $this->logger->info($e->getMessage());
                    }
                }
            }
        }

        $this->em->flush();
        $this->logger->info(sprintf('Finish process'));
    }

    /**
     * @param $customersIds
     * @param $integrationIds
     *
     * @return \Oro\Bundle\BatchBundle\ORM\Query\BufferedQueryResultIterator
     */
    protected function getCustomerIterator($customersIds, $integrationId)
    {
        $repository = $this->em->getRepository('OroCRMMagentoBundle:Customer');

        return $repository->getIteratorByIdsAndIntegrationIds($customersIds, [$integrationId]);
    }

    /**
     * @param Address $customerAddress
     * @param Contact $contact
     *
     * @return ContactAddress
     */
    protected function createContactAddress(Address $customerAddress, Contact $contact)
    {
        $newContactAddress = new ContactAddress();
        $this->copyProperties($this->baseAddressProperties, $newContactAddress, $customerAddress);
        $newContactAddress->setOwner($contact);
        $newContactAddress->setPrimary($customerAddress->isPrimary());

        return $newContactAddress;
    }

    /**
     * @param ContactAddress $contactAddress
     * @param Address $customerAddress
     *
     * @return ContactAddress
     */
    protected function updateContactAddress(ContactAddress $contactAddress, Address $customerAddress)
    {
        $this->copyProperties($this->baseAddressProperties, $contactAddress, $customerAddress);

        return $contactAddress;
    }

    /**
     * @param array $listProperties
     * @param $firstEntity
     * @param $secondEntity
     */
    protected function copyProperties($listProperties, $firstEntity, $secondEntity)
    {
        foreach ($listProperties as $property) {
            try {
                $this->accessor->setValue(
                    $firstEntity,
                    $property,
                    $this->accessor->getValue($secondEntity, $property)
                );
            } catch (\Exception $e) {
                $this->logger->info($e->getMessage());
            }
        }
    }

    /**
     * @param Address $customerAddress
     * @param Address $magentoAddress
     *
     * @return Address
     */
    protected function updateAddressByDataFromMagento(Address $customerAddress, Address $magentoAddress)
    {
        $properties = $this->baseAddressProperties;
        $properties[] = 'phone';

        $this->copyProperties($properties, $customerAddress, $magentoAddress);

        return $customerAddress;
    }

    /**
     * @return $this
     */
    protected function getContextProcessor()
    {
        $entityName = 'OroCRM\Bundle\MagentoBundle\Entity\Address';
        $this->contextProcessor->setEntityName($entityName);
        $this->customerAddressStrategy->setEntityName($entityName);
        $this->contextProcessor->setStrategy($this->customerAddressStrategy);

        return $this;
    }

    /**
     * @param $context
     *
     * @return $this
     */
    protected function setContext($context)
    {
        $this->contextProcessor->setImportExportContext($context);

        return $this;
    }

    /**
     * @param Customer $customer
     * @param $id
     *
     * @return Address|null
     * @throws \Exception
     */
    protected function findCustomerAddress(Customer $customer, $id)
    {
        $address = $customer->getAddresses()->filter(function (Address $address) use ($id) {
            return $address->getOriginId() === $id;
        });

        switch (true) {
            case ($address->count() === 1):
                $response = $address->first();
                break;
            case ($address->count() > 1):
                throw new ManyAddressesException("Customer has serveral address with origin id " . $id);
                break;
            default:
                $response = null;
        }

        return $response;
    }

    /**
     * @param array $data
     * @param Channel $integration
     *
     * @return mixed|null
     */
    protected function convertDataIntoAddress($data, Channel $integration)
    {
        $customerAddress = $this->customerAddressDataConverter->convertToImportFormat($data);
        $customerAddress['channel'] = ['id' => $integration->getId()];

        $context = new Context([]);
        $context->setValue('itemData', $customerAddress);

        $this->getContextProcessor()->setContext($context);

        return $this->contextProcessor->process($customerAddress);
    }

    /**
     * @param Address $customerAddress
     * @param Address $address
     * @param Contact $contact
     *
     * @return ContactAddress
     */
    private function handleContactAddress($customerAddress, $address, $contact)
    {
        if ($customerAddress->getContactAddress()) {
            $contactAddress = $this->updateContactAddress(
                $customerAddress->getContactAddress(),
                $address
            );
        } else {
            $contactAddress = $this->createContactAddress($address, $contact);
        }

        return $contactAddress;
    }
}
