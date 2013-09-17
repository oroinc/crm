<?php

namespace OroCRM\Bundle\ContactBundle\ImportExport\Provider;

use Symfony\Component\Serializer\SerializerInterface;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityRepository;

use Oro\Bundle\ImportExportBundle\Converter\DataConverterInterface;
use OroCRM\Bundle\ContactBundle\Entity\Contact;
use OroCRM\Bundle\ContactBundle\Entity\ContactAddress;
use OroCRM\Bundle\ContactBundle\Entity\ContactEmail;
use OroCRM\Bundle\ContactBundle\Entity\ContactPhone;
use OroCRM\Bundle\ContactBundle\Entity\Group;
use OroCRM\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\AddressBundle\Entity\AddressType;

class ContactHeaderProvider
{
    /**
     * @var ManagerRegistry
     */
    protected $managerRegistry;

    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * @var DataConverterInterface
     */
    protected $dataConverter;

    /**
     * @var ContactMaxDataProvider
     */
    protected $maxDataProvider;

    /**
     * @var array
     */
    protected $maxHeader;

    /**
     * @param ManagerRegistry $managerRegistry
     * @param SerializerInterface $serializer
     * @param DataConverterInterface $dataConverter
     * @param ContactMaxDataProvider $maxDataProvider
     */
    public function __construct(
        ManagerRegistry $managerRegistry,
        SerializerInterface $serializer,
        DataConverterInterface $dataConverter,
        ContactMaxDataProvider $maxDataProvider
    ) {
        $this->managerRegistry = $managerRegistry;
        $this->serializer      = $serializer;
        $this->dataConverter   = $dataConverter;
        $this->maxDataProvider = $maxDataProvider;
    }

    /**
     * @return array
     */
    public function getHeader()
    {
        if (null === $this->maxHeader) {
            $contact = $this->getMaxContactEntity();
            $complexContactData = $this->serializer->serialize($contact, null);
            $plainContactData = $this->dataConverter->convertToExportFormat($complexContactData);
            $this->maxHeader = array_keys($plainContactData);
        }

        return $this->maxHeader;
    }

    /**
     * @return Contact
     */
    protected function getMaxContactEntity()
    {
        $contact = new Contact();
        $contact->setOwner(new User());
        $contact->setAssignedTo(new User());

        $maxAccounts = $this->maxDataProvider->getMaxAccountsCount();
        for ($i = 0; $i < $maxAccounts; $i++) {
            $contact->addAccount(new Account());
        }

        /** @var AddressType[] $allAddressTypes */
        $allAddressTypes = $this->getAllAddressTypes();

        $maxAddresses = $this->maxDataProvider->getMaxAddressesCount();
        for ($i = 0; $i < $maxAddresses; $i++) {
            $contactAddress = new ContactAddress();
            foreach ($allAddressTypes as $addressType) {
                $contactAddress->addType($addressType);
            }
            $contact->addAddress($contactAddress);
        }

        $maxEmails = $this->maxDataProvider->getMaxEmailsCount();
        for ($i = 0; $i < $maxEmails; $i++) {
            $contact->addEmail(new ContactEmail());
        }

        $maxPhones = $this->maxDataProvider->getMaxPhonesCount();
        for ($i = 0; $i < $maxPhones; $i++) {
            $contact->addPhone(new ContactPhone());
        }

        $maxGroups = $this->maxDataProvider->getMaxGroupsCount();
        for ($i = 0; $i < $maxGroups; $i++) {
            $contact->addGroup(new Group());
        }

        return $contact;
    }

    /**
     * @return array
     */
    protected function getAllAddressTypes()
    {
        /** @var EntityRepository $repository */
        $repository = $this->managerRegistry->getRepository('OroAddressBundle:AddressType');
        return $repository->findAll();
    }
}
