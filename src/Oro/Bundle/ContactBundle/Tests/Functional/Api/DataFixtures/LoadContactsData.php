<?php

namespace Oro\Bundle\ContactBundle\Tests\Functional\Api\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;

use Oro\Bundle\ContactBundle\Entity\ContactEmail;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\TestFrameworkBundle\Tests\Functional\DataFixtures\LoadOrganization;
use Oro\Bundle\TestFrameworkBundle\Tests\Functional\DataFixtures\LoadUser;

class LoadContactsData extends AbstractFixture implements ContainerAwareInterface, DependentFixtureInterface
{
    use ContainerAwareTrait;

    /** @var EntityManager */
    protected $em;

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            LoadOrganization::class,
            LoadUser::class
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->em = $manager;
        try {
            $this->createAccount(1);
            $this->createContact(1);
        } finally {
            $this->em = null;
        }
    }

    /**
     * @param int $number
     */
    protected function createAccount($number)
    {
        $account = new Account();
        $account->setName(sprintf('Account %d', $number));
        $account->setOrganization($this->getReference('organization'));

        $this->em->persist($account);
        $this->em->flush();
        $this->setReference(sprintf('account%d', $number), $account);
    }

    /**
     * @param int $number
     */
    protected function createContact($number)
    {
        $contact = new Contact();
        $contact->setFirstName(sprintf('Contact %d', $number));
        $contact->setOrganization($this->getReference('organization'));
        $contact->setOwner($this->getReference('user'));
        $contact->setBirthday(new \DateTime('1973-03-07', new \DateTimeZone('UTC')));
        $contact->setCreatedBy($this->getReference('user'));

        $email1 = new ContactEmail(sprintf('contact%d_1@example.com', $number));
        $contact->addEmail($email1);
        $email2 = new ContactEmail(sprintf('contact%d_2@example.com', $number));
        $email2->setPrimary(true);
        $contact->addEmail($email2);

        $this->em->persist($contact);
        $this->em->flush();
        $this->setReference(sprintf('contact%d', $number), $contact);
    }
}
