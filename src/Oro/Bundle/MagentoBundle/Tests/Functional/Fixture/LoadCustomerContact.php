<?php

namespace Oro\Bundle\MagentoBundle\Tests\Functional\Fixture;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\ContactBundle\Entity\ContactEmail;
use Oro\Bundle\MagentoBundle\Entity\Customer;
use Oro\Bundle\UserBundle\Entity\UserManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
        $admin = $userManager->findUserByUsername('admin');
        $this->setReference('admin_user', $admin);
        $contact->setOwner($admin);

        $contact->setOrganization($manager->getRepository('OroOrganizationBundle:Organization')->getFirst());

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
        return ['Oro\\Bundle\\MagentoBundle\\Tests\\Functional\\Fixture\\LoadMagentoChannel'];
    }

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
}
