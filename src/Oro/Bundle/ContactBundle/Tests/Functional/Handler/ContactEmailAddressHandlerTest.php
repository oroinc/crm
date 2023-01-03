<?php

namespace Oro\Bundle\ContactBundle\Tests\Functional\Handler;

use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\ContactBundle\Handler\ContactEmailAddressHandler;
use Oro\Bundle\ContactBundle\Tests\Functional\DataFixtures\LoadContactEmailData;
use Oro\Bundle\ContactBundle\Tests\Functional\DataFixtures\LoadContactEntitiesData;
use Oro\Bundle\EmailBundle\Entity\Email;
use Oro\Bundle\EmailBundle\Entity\EmailAddress;
use Oro\Bundle\EmailBundle\Entity\EmailRecipient;
use Oro\Bundle\EmailBundle\Entity\Manager\EmailAddressManager;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class ContactEmailAddressHandlerTest extends WebTestCase
{
    protected function setUp(): void
    {
        $this->initClient([], self::generateBasicAuthHeader());
        $listenerManager = $this->getContainer()->get('oro_platform.optional_listeners.manager');
        $listenerManager->disableListener('oro_email.listener.entity_listener');
        $this->loadFixtures([LoadContactEmailData::class]);
    }

    protected function tearDown(): void
    {
        $listenerManager = $this->getContainer()->get('oro_platform.optional_listeners.manager');
        $listenerManager->enableListener('oro_email.listener.entity_listener');
    }

    public function testActualizeContactEmailAssociations()
    {
        $registry = $this->getContainer()->get('doctrine');
        $em = $registry->getManagerForClass(Email::class);

        /** @var Contact $contact */
        $contact = $this->getReference('Contact_' . LoadContactEntitiesData::FIRST_ENTITY_NAME);
        /** @var EmailAddressManager $emailAddressManager */
        $emailAddressManager = $this->getContainer()->get('oro_email.email.address.manager');
        $detachedEmailAddress1 = $this->createEmailAddress($emailAddressManager, $contact, 'detached@test.com');
        $detachedEmailAddress2 = $this->createEmailAddress(
            $emailAddressManager,
            $contact,
            'detached_with_email@test.com'
        );
        $detachedEmailAddress3 = $this->createEmailAddress(
            $emailAddressManager,
            $contact,
            'detached_with_recipient@test.com'
        );
        $emailAddressManager->getEntityManager()->flush([
            $detachedEmailAddress1,
            $detachedEmailAddress2,
            $detachedEmailAddress3
        ]);

        $emailAddresses = $this->getEmailAddresses($emailAddressManager);
        $this->assertCount(3, $emailAddresses);
        $this->assertEqualsCanonicalizing(
            [
                $detachedEmailAddress1->getEmail(),
                $detachedEmailAddress2->getEmail(),
                $detachedEmailAddress3->getEmail()
            ],
            $emailAddresses
        );

        $email = $this->createEmail($detachedEmailAddress2);
        $em->persist($email);

        $emailRecipient = $this->createEmailRecipient($detachedEmailAddress3);
        $em->persist($emailRecipient);

        $em->flush();

        $handler = new ContactEmailAddressHandler(
            $this->getContainer()->get('oro_entity.orm.insert_from_select_query_executor'),
            $emailAddressManager,
            $registry
        );
        $handler->actualizeContactEmailAssociations();
        $emailAddresses = $this->getEmailAddresses($emailAddressManager);
        $this->assertCount(6, $emailAddresses);
        $this->assertEqualsCanonicalizing(
            [
                'detached_with_email@test.com',
                'detached_with_recipient@test.com',
                'test1@test.test',
                'test2@test.test',
                'test3@test.test',
                'test4@test.test'
            ],
            $emailAddresses
        );
    }

    private function getEmailAddresses(EmailAddressManager $emailAddressManager): array
    {
        $repo = $emailAddressManager->getEmailAddressRepository();
        $emailAddresses = array_filter($repo->findAll(), function (EmailAddress $emailAddress) {
            return $emailAddress->getOwner() instanceof Contact;
        });

        return array_values(
            array_map(
                function (EmailAddress $emailAddress) {
                    return $emailAddress->getEmail();
                },
                $emailAddresses
            )
        );
    }

    private function createEmailAddress(
        EmailAddressManager $emailAddressManager,
        Contact $contact,
        string $email
    ): EmailAddress {
        $detachedEmailAddress = $emailAddressManager->newEmailAddress();
        $detachedEmailAddress->setOwner($contact);
        $detachedEmailAddress->setEmail($email);

        $emailEm = $emailAddressManager->getEntityManager();
        $emailEm->persist($detachedEmailAddress);

        return $detachedEmailAddress;
    }

    private function createEmailRecipient(EmailAddress $emailAddress): EmailRecipient
    {
        $emailRecipient = new EmailRecipient();
        $emailRecipient->setName('Test');
        $emailRecipient->setType(EmailRecipient::TO);
        $emailRecipient->setEmailAddress($emailAddress);

        return $emailRecipient;
    }

    private function createEmail(EmailAddress $emailAddress): Email
    {
        $now = new \DateTime('now', new \DateTimeZone('UTC'));
        $email = new Email();
        $email->setSubject('Test');
        $email->setFromName('Test');
        $email->setSentAt($now);
        $email->setInternalDate($now);
        $email->setMessageId('MID');
        $email->setFromEmailAddress($emailAddress);

        return $email;
    }
}
