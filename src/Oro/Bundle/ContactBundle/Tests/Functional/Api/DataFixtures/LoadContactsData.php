<?php

namespace Oro\Bundle\ContactBundle\Tests\Functional\Api\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\ContactBundle\Entity\ContactEmail;
use Oro\Bundle\ContactBundle\Entity\ContactPhone;
use Oro\Bundle\TestFrameworkBundle\Tests\Functional\DataFixtures\LoadOrganization;
use Oro\Bundle\TestFrameworkBundle\Tests\Functional\DataFixtures\LoadUser;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

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
            $account = $this->createAccount(1);
            $contact1 = $this->createContact(1);
            $contact2 = $this->createContact(2, true);

            $account->setDefaultContact($contact1);
            $account->addContact($contact1);
            $account->addContact($contact2);
            $manager->flush();
        } finally {
            $this->em = null;
        }
    }

    /**
     * @param int $number
     *
     * @return Account
     */
    protected function createAccount($number)
    {
        $account = new Account();
        $account->setName(sprintf('Account %d', $number));
        $account->setOrganization($this->getReference('organization'));

        $this->em->persist($account);
        $this->em->flush();
        $this->setReference(sprintf('account%d', $number), $account);

        return $account;
    }

    /**
     * @param int  $number
     * @param bool $withoutEmailsAndPhones
     *
     * @return Contact
     */
    protected function createContact($number, $withoutEmailsAndPhones = false)
    {
        $contact = new Contact();
        $contact->setFirstName(sprintf('Contact %d', $number));
        $contact->setOrganization($this->getReference('organization'));
        $contact->setOwner($this->getReference('user'));
        $contact->setBirthday(new \DateTime('1973-03-07', new \DateTimeZone('UTC')));
        $contact->setCreatedBy($this->getReference('user'));

        if (!$withoutEmailsAndPhones) {
            $email1 = new ContactEmail(sprintf('contact%d_1@example.com', $number));
            $contact->addEmail($email1);
            $email2 = new ContactEmail(sprintf('contact%d_2@example.com', $number));
            $email2->setPrimary(true);
            $contact->addEmail($email2);

            $phone1 = new ContactPhone(sprintf('555666%d111', $number));
            $contact->addPhone($phone1);
            $phone2 = new ContactPhone(sprintf('555666%d112', $number));
            $phone2->setPrimary(true);
            $contact->addPhone($phone2);
        }

        $this->em->persist($contact);
        $this->em->flush();
        $this->setReference(sprintf('contact%d', $number), $contact);

        return $contact;
    }
}
