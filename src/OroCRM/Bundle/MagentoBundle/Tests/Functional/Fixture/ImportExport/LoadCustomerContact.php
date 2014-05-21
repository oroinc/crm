<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Functional\Fixture\ImportExport;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

use OroCRM\Bundle\ContactBundle\Entity\Contact;
use OroCRM\Bundle\MagentoBundle\Entity\Customer;
use OroCRM\Bundle\ContactBundle\Entity\ContactEmail;

class LoadCustomerContact extends AbstractFixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        /** @var Customer $customer */
        $customer = $this->getReference('customer');

        $email = new ContactEmail();
        $email->setPrimary(true);
        $email->setEmail($customer->getEmail());

        $contact = new Contact();
        $contact->setFirstName($customer->getFirstName());
        $contact->setLastName($customer->getLastName());
        $contact->setGender($customer->getGender());
        $contact->addEmail($email);

        $customer->setContact($contact);

        $this->setReference('contact', $contact);

        $manager->persist($contact);

        $manager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return ['OroCRM\\Bundle\\MagentoBundle\\Tests\\Functional\\Fixture\\LoadMagentoChannel'];
    }
}
