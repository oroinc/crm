<?php

namespace Oro\Bundle\MagentoBundle\Manager;

use Doctrine\ORM\EntityManager;
use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\ContactBundle\Entity\ContactAddress;
use Oro\Bundle\MagentoBundle\Entity\Address;
use Oro\Bundle\MagentoBundle\Entity\Customer;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

class CustomerAddressManager implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /** @var EntityManager */
    protected $em;

    /** @var PropertyAccessor  */
    protected $accessor;

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
        'nameSuffix'
    ];

    /**
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
        $this->accessor = PropertyAccess::createPropertyAccessor();
    }

    /**
     * @param int[]|null $customersIds
     * @param int[]|null $integrationIds
     * @param int $batchSize
     */
    public function copyToContact($customersIds = null, $integrationIds = null, $batchSize = 25)
    {
        $i = 0;
        $this->logger->info(sprintf('Start process'));
        $repository = $this->em->getRepository('OroMagentoBundle:Customer');

        $iterator = $repository->getIteratorByIdsAndIntegrationIds($customersIds, $integrationIds);
        $iterator->setBufferSize($batchSize);
        $customerCount = $iterator->count();

        $iterator->setPageCallback(function () use (&$i, $customerCount) {
            $this->em->flush();
            $this->em->flush();
            $this->logger->info(sprintf('Processed %s customers from %s', $i, $customerCount));
        });

        /** @var Customer $customer */
        foreach ($iterator as $customer) {
            $i++;
            $contact = $customer->getContact();
            if ($contact) {
                $addresses = $customer->getAddresses();

                if ($addresses->count() > 0) {
                    foreach ($addresses as $address) {
                        $newContactAddress = $this->convertToContactAddress($address);
                        if (!$this->contactHasAddress($contact, $newContactAddress)) {
                            $contact->addAddress($newContactAddress);
                            $message = 'Customer address with id=%s was copied in contact with id=%s';
                            $this->logger->info(sprintf($message, $address->getId(), $contact->getId()));
                        }
                    }
                    $this->em->persist($contact);
                }
            }
        }

        $this->em->flush();
        $this->logger->info(sprintf('Finish process'));
    }

    /**
     * @param Contact $contact
     * @param ContactAddress $contactAddress
     *
     * @return bool
     */
    protected function contactHasAddress(Contact $contact, ContactAddress $contactAddress)
    {
        $addresses = $contact->getAddresses();

        foreach ($addresses as $address) {
            if ($this->isEqualAddresses($address, $contactAddress)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param ContactAddress $address1
     * @param ContactAddress $address2
     *
     * @return bool
     */
    protected function isEqualAddresses(ContactAddress $address1, ContactAddress $address2)
    {
        $countEqualProperty = 0;
        foreach ($this->baseAddressProperties as $property) {
            if ($this->accessor->getValue($address1, $property) === $this->accessor->getValue($address2, $property)) {
                $countEqualProperty++;
            }
        }

        return $countEqualProperty === count($this->baseAddressProperties);
    }

    /**
     * @param Address $customerAddress
     *
     * @return ContactAddress
     */
    protected function convertToContactAddress(Address $customerAddress)
    {
        $properties = $this->baseAddressProperties;
        $properties[] = 'types';
        $properties[] = 'primary';

        $contactAddress = new ContactAddress();
        foreach ($properties as $property) {
            $this->accessor->setValue(
                $contactAddress,
                $property,
                $this->accessor->getValue($customerAddress, $property)
            );
        }

        return $contactAddress;
    }
}
