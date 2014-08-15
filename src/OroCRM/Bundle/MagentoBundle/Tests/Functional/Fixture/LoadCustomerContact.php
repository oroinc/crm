<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Functional\Fixture;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;



use Oro\Bundle\UserBundle\Entity\UserManager;
use OroCRM\Bundle\ContactBundle\Entity\Contact;
use OroCRM\Bundle\ContactBundle\Entity\ContactEmail;
use OroCRM\Bundle\MagentoBundle\Entity\Customer;

class LoadCustomerContact extends AbstractFixture implements DependentFixtureInterface, ContainerAwareInterface
{
    /** @var ContainerInterface */
    private $container;

    /**
     * {@inheritdoc}
     */
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

        /** @var UserManager $userManager */
        $userManager = $this->container->get('oro_user.manager');
        $admin       = $userManager->loadUserByUsername('admin');
        $this->setReference('admin_user', $admin);
        $contact->setOwner($admin);

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

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
}
