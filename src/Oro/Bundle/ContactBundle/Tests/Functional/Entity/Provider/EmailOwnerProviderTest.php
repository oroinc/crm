<?php

namespace Oro\Bundle\ContactBundle\Tests\Functional\Entity\Provider;

use Doctrine\DBAL\Platforms\MySqlPlatform;
use Doctrine\ORM\EntityManagerInterface;
use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\ContactBundle\Entity\ContactEmail;
use Oro\Bundle\EmailBundle\Entity\Provider\EmailOwnerProviderInterface;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class EmailOwnerProviderTest extends WebTestCase
{
    protected function setUp(): void
    {
        $this->initClient();
        $this->loadFixtures([
            '@OroContactBundle/Tests/Functional/Entity/Provider/DataFixtures/email_owner_provider.yml'
        ]);
    }

    private function getProvider(): EmailOwnerProviderInterface
    {
        return self::getContainer()->get('oro_contact.email.owner.provider');
    }

    private function getEntityManager(): EntityManagerInterface
    {
        return self::getContainer()->get('doctrine')->getManagerForClass(Contact::class);
    }

    private function assertCaseInsensitiveSearchSupported(): void
    {
        $em = $this->getEntityManager();
        $conn = $em->getConnection();
        if ($conn->getDatabasePlatform() instanceof MySqlPlatform) {
            $supported = (bool)$conn->fetchAll(
                'SELECT 1 FROM information_schema.columns WHERE '
                . 'TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ? AND COLLATION_NAME LIKE ? LIMIT 1;',
                [$conn->getDatabase(), $em->getClassMetadata(ContactEmail::class)->getTableName(), 'email', '%_ci']
            );
            if (!$supported) {
                self::markTestSkipped('Case insensitive email search is not supported.');
            }
        }
    }

    public function caseInsensitiveSearchDataProvider(): array
    {
        return [[true], [false]];
    }

    public function testGetEmailOwnerClass(): void
    {
        self::assertEquals(Contact::class, $this->getProvider()->getEmailOwnerClass());
    }

    /**
     * @dataProvider caseInsensitiveSearchDataProvider
     */
    public function testFindEmailOwner(bool $caseInsensitiveSearch): void
    {
        $email = 'jane.smith@example.com';
        if ($caseInsensitiveSearch) {
            $this->assertCaseInsensitiveSearchSupported();
            $email = strtoupper($email);
        }

        /** @var Contact $owner */
        $owner = $this->getProvider()->findEmailOwner($this->getEntityManager(), $email);
        self::assertInstanceOf(Contact::class, $owner);
        self::assertSame($this->getReference('contact4')->getId(), $owner->getId());
    }

    public function testFindEmailOwnerWhenItDoesNotExist(): void
    {
        $owner = $this->getProvider()->findEmailOwner($this->getEntityManager(), 'another@example.com');
        self::assertNull($owner);
    }

    public function testFindEmailOwnerWhenEmailDuplicated(): void
    {
        $owner = $this->getProvider()->findEmailOwner($this->getEntityManager(), 'test@example.com');
        self::assertInstanceOf(Contact::class, $owner);
        self::assertSame($this->getReference('contact2')->getId(), $owner->getId());
    }

    /**
     * @dataProvider caseInsensitiveSearchDataProvider
     */
    public function testGetOrganizations(bool $caseInsensitiveSearch): void
    {
        $email = 'jane.smith@example.com';
        if ($caseInsensitiveSearch) {
            $this->assertCaseInsensitiveSearchSupported();
            $email = strtoupper($email);
        }

        $organizations = $this->getProvider()->getOrganizations($this->getEntityManager(), $email);
        self::assertSame(
            [$this->getReference('organization')->getId()],
            $organizations
        );
    }

    /**
     * @dataProvider caseInsensitiveSearchDataProvider
     */
    public function testGetOrganizationsForSeveralOrganizations(bool $caseInsensitiveSearch): void
    {
        $email = 'john.smith@example.com';
        if ($caseInsensitiveSearch) {
            $this->assertCaseInsensitiveSearchSupported();
            $email = strtoupper($email);
        }

        $organizations = $this->getProvider()->getOrganizations($this->getEntityManager(), $email);
        sort($organizations);
        self::assertSame(
            [$this->getReference('organization')->getId(), $this->getReference('another_organization')->getId()],
            $organizations
        );
    }

    public function testGetEmails(): void
    {
        $emails = $this->getProvider()->getEmails(
            $this->getEntityManager(),
            $this->getReference('organization')->getId()
        );
        self::assertSame(
            [
                'john.smith@example.com',
                'test@example.com',
                'test@example.com',
                'test@example.com',
                'jane.smith@example.com',
            ],
            iterator_to_array($emails)
        );
    }
}
