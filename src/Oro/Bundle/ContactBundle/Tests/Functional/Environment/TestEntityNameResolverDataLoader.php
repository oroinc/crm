<?php

namespace Oro\Bundle\ContactBundle\Tests\Functional\Environment;

use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\ContactBundle\Entity\ContactAddress;
use Oro\Bundle\ContactBundle\Entity\ContactEmail;
use Oro\Bundle\ContactBundle\Entity\ContactPhone;
use Oro\Bundle\ContactBundle\Entity\Group;
use Oro\Bundle\EntityBundle\Provider\EntityNameProviderInterface;
use Oro\Bundle\EntityBundle\Tests\Functional\Environment\TestEntityNameResolverDataLoaderInterface;

class TestEntityNameResolverDataLoader implements TestEntityNameResolverDataLoaderInterface
{
    private TestEntityNameResolverDataLoaderInterface $innerDataLoader;

    public function __construct(TestEntityNameResolverDataLoaderInterface $innerDataLoader)
    {
        $this->innerDataLoader = $innerDataLoader;
    }

    public function loadEntity(
        EntityManagerInterface $em,
        ReferenceRepository $repository,
        string $entityClass
    ): array {
        if (Contact::class === $entityClass) {
            $contact = new Contact();
            $contact->setOrganization($repository->getReference('organization'));
            $contact->setOwner($repository->getReference('user'));
            $contact->setFirstName('John');
            $contact->setMiddleName('M');
            $contact->setLastName('Doo');
            $contact->addEmail($this->createContactEmail('c11@example.com', true));
            $contact->addEmail($this->createContactEmail('c12@example.com'));
            $contact->addPhone($this->createContactPhone('123-456', true));
            $contact->addPhone($this->createContactPhone('123-457'));
            $repository->setReference('contact', $contact);
            $em->persist($contact);

            $contactWithoutName = new Contact();
            $contactWithoutName->setOrganization($repository->getReference('organization'));
            $contactWithoutName->setOwner($repository->getReference('user'));
            $contactWithoutName->addEmail($this->createContactEmail('c21@example.com', true));
            $contactWithoutName->addEmail($this->createContactEmail('c22@example.com'));
            $contactWithoutName->addPhone($this->createContactPhone('234-567', true));
            $contactWithoutName->addPhone($this->createContactPhone('234-568'));
            $repository->setReference('contactWithoutName', $contactWithoutName);
            $em->persist($contactWithoutName);

            $contactWithoutNameAndEmail = new Contact();
            $contactWithoutNameAndEmail->setOrganization($repository->getReference('organization'));
            $contactWithoutNameAndEmail->setOwner($repository->getReference('user'));
            $contactWithoutNameAndEmail->addPhone($this->createContactPhone('345-678', true));
            $contactWithoutNameAndEmail->addPhone($this->createContactPhone('345-679'));
            $repository->setReference('contactWithoutNameAndEmail', $contactWithoutNameAndEmail);
            $em->persist($contactWithoutNameAndEmail);

            $contactWithoutNameAndEmailAndPhone = new Contact();
            $contactWithoutNameAndEmailAndPhone->setOrganization($repository->getReference('organization'));
            $contactWithoutNameAndEmailAndPhone->setOwner($repository->getReference('user'));
            $repository->setReference('contactWithoutNameAndEmailAndPhone', $contactWithoutNameAndEmailAndPhone);
            $em->persist($contactWithoutNameAndEmailAndPhone);

            $em->flush();

            return [
                'contact',
                'contactWithoutName',
                'contactWithoutNameAndEmail',
                'contactWithoutNameAndEmailAndPhone'
            ];
        }

        if (ContactAddress::class === $entityClass) {
            $contact = new Contact();
            $contact->setOrganization($repository->getReference('organization'));
            $contact->setOwner($repository->getReference('user'));
            $contact->setFirstName('John');
            $contact->setLastName('Doo');
            $em->persist($contact);
            $contactAddress = new ContactAddress();
            $contactAddress->setOrganization($repository->getReference('organization'));
            $contactAddress->setOwner($contact);
            $contactAddress->setFirstName('Jane');
            $contactAddress->setMiddleName('M');
            $contactAddress->setLastName('Doo');
            $repository->setReference('contactAddress', $contactAddress);
            $em->persist($contactAddress);
            $em->flush();

            return ['contactAddress'];
        }

        if (Group::class === $entityClass) {
            $contactGroup = new Group();
            $contactGroup->setOrganization($repository->getReference('organization'));
            $contactGroup->setOwner($repository->getReference('user'));
            $contactGroup->setLabel('Test Contact Group');
            $repository->setReference('contactGroup', $contactGroup);
            $em->persist($contactGroup);
            $em->flush();

            return ['contactGroup'];
        }

        return $this->innerDataLoader->loadEntity($em, $repository, $entityClass);
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function getExpectedEntityName(
        ReferenceRepository $repository,
        string $entityClass,
        string $entityReference,
        ?string $format,
        ?string $locale
    ): string {
        if (Contact::class === $entityClass) {
            if ('contact' === $entityReference) {
                return EntityNameProviderInterface::SHORT === $format
                    ? 'John'
                    : 'John M Doo';
            }
            if ('contactWithoutName' === $entityReference) {
                return EntityNameProviderInterface::SHORT === $format
                    ? (string)$repository->getReference($entityReference)->getId()
                    : 'c21@example.com';
            }
            if ('contactWithoutNameAndEmail' === $entityReference) {
                return EntityNameProviderInterface::SHORT === $format
                    ? (string)$repository->getReference($entityReference)->getId()
                    : '345-678';
            }
            if ('contactWithoutNameAndEmailAndPhone' === $entityReference) {
                return EntityNameProviderInterface::SHORT === $format
                    ? (string)$repository->getReference($entityReference)->getId()
                    : '';
            }
        }
        if (ContactAddress::class === $entityClass) {
            return EntityNameProviderInterface::SHORT === $format
                ? 'Jane'
                : 'Jane M Doo';
        }
        if (Group::class === $entityClass) {
            return 'Test Contact Group';
        }

        return $this->innerDataLoader->getExpectedEntityName(
            $repository,
            $entityClass,
            $entityReference,
            $format,
            $locale
        );
    }

    private function createContactEmail(string $email, bool $primary = false): ContactEmail
    {
        $contactEmail = new ContactEmail();
        $contactEmail->setEmail($email);
        $contactEmail->setPrimary($primary);

        return $contactEmail;
    }

    private function createContactPhone(string $phone, bool $primary = false): ContactPhone
    {
        $contactPhone = new ContactPhone();
        $contactPhone->setPhone($phone);
        $contactPhone->setPrimary($primary);

        return $contactPhone;
    }
}
