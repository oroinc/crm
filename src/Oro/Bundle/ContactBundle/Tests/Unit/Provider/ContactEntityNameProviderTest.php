<?php

namespace Oro\Bundle\ContactBundle\Tests\Unit\Provider;

use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\ContactBundle\Entity\ContactEmail;
use Oro\Bundle\ContactBundle\Entity\ContactPhone;
use Oro\Bundle\ContactBundle\Provider\ContactEntityNameProvider;
use Oro\Bundle\EntityBundle\Provider\EntityNameProviderInterface;
use Oro\Bundle\LocaleBundle\DQL\DQLNameFormatter;
use Oro\Bundle\LocaleBundle\Formatter\NameFormatter;
use Oro\Component\DependencyInjection\ServiceLink;

class ContactEntityNameProviderTest extends \PHPUnit\Framework\TestCase
{
    /** @var NameFormatter|\PHPUnit\Framework\MockObject\MockObject */
    private $nameFormatter;

    /** @var DQLNameFormatter|\PHPUnit\Framework\MockObject\MockObject */
    private $dqlNameFormatter;

    /** @var ContactEntityNameProvider */
    private $provider;

    protected function setUp(): void
    {
        $this->nameFormatter = $this->createMock(NameFormatter::class);
        $this->dqlNameFormatter = $this->createMock(DQLNameFormatter::class);

        $nameFormatterLink = $this->createMock(ServiceLink::class);
        $nameFormatterLink->expects(self::any())
            ->method('getService')
            ->willReturn($this->nameFormatter);

        $dqlNameFormatterLink = $this->createMock(ServiceLink::class);
        $dqlNameFormatterLink->expects(self::any())
            ->method('getService')
            ->willReturn($this->dqlNameFormatter);

        $this->provider = new ContactEntityNameProvider($nameFormatterLink, $dqlNameFormatterLink);
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

    public function testGetNameForUnsupportedEntity(): void
    {
        self::assertFalse(
            $this->provider->getName(EntityNameProviderInterface::FULL, 'en', new \stdClass())
        );
    }

    public function testGetNameForUnsupportedFormat(): void
    {
        self::assertFalse(
            $this->provider->getName(EntityNameProviderInterface::SHORT, 'en', new Contact())
        );
    }

    public function testGetNameWhenContactHasName(): void
    {
        $contact = new Contact();
        $contact->setFirstName('John');
        $contact->setLastName('Doo');
        $contact->addEmail($this->createContactEmail('c11@example.com', true));
        $contact->addEmail($this->createContactEmail('c12@example.com'));
        $contact->addPhone($this->createContactPhone('123-456', true));
        $contact->addPhone($this->createContactPhone('123-457'));

        $this->nameFormatter->expects(self::once())
            ->method('format')
            ->willReturn('John Doo');

        self::assertSame(
            'John Doo',
            $this->provider->getName(EntityNameProviderInterface::FULL, 'en', $contact)
        );
    }

    public function testGetNameForContactWithoutName(): void
    {
        $contact = new Contact();
        $contact->addEmail($this->createContactEmail('c11@example.com', true));
        $contact->addEmail($this->createContactEmail('c12@example.com'));
        $contact->addPhone($this->createContactPhone('123-456', true));
        $contact->addPhone($this->createContactPhone('123-457'));

        $this->nameFormatter->expects(self::once())
            ->method('format')
            ->willReturn('');

        self::assertSame(
            'c11@example.com',
            $this->provider->getName(EntityNameProviderInterface::FULL, 'en', $contact)
        );
    }

    public function testGetNameForContactWithoutNameAndEmail(): void
    {
        $contact = new Contact();
        $contact->addPhone($this->createContactPhone('123-456', true));
        $contact->addPhone($this->createContactPhone('123-457'));

        $this->nameFormatter->expects(self::once())
            ->method('format')
            ->willReturn('');

        self::assertSame(
            '123-456',
            $this->provider->getName(EntityNameProviderInterface::FULL, 'en', $contact)
        );
    }

    public function testGetNameForContactWithoutNameAndEmailAndPhone(): void
    {
        $contact = new Contact();

        $this->nameFormatter->expects(self::once())
            ->method('format')
            ->willReturn('');

        self::assertSame(
            '',
            $this->provider->getName(EntityNameProviderInterface::FULL, 'en', $contact)
        );
    }

    public function testGetNameDQLForUnsupportedEntity(): void
    {
        self::assertFalse(
            $this->provider->getNameDQL(EntityNameProviderInterface::FULL, 'en', \stdClass::class, 'entity')
        );
    }

    public function testGetNameDQLForUnsupportedFormat(): void
    {
        self::assertFalse(
            $this->provider->getNameDQL(EntityNameProviderInterface::SHORT, 'en', Contact::class, 'contact')
        );
    }

    public function testGetNameDQL(): void
    {
        $this->dqlNameFormatter->expects(self::once())
            ->method('getFormattedNameDQL')
            ->willReturn('Contact');

        self::assertEquals(
            'COALESCE(NULLIF(Contact, \'\'),'
            . ' CAST((SELECT contact_emails.email FROM Oro\Bundle\ContactBundle\Entity\Contact contact_emails_base'
            . ' LEFT JOIN contact_emails_base.emails contact_emails'
            . ' WHERE contact_emails.primary = true AND contact_emails_base = contact) AS string),'
            . ' CAST((SELECT contact_phones.phone FROM Oro\Bundle\ContactBundle\Entity\Contact contact_phones_base'
            . ' LEFT JOIN contact_phones_base.phones contact_phones'
            . ' WHERE contact_phones.primary = true AND contact_phones_base = contact) AS string), \'\')',
            $this->provider->getNameDQL(EntityNameProviderInterface::FULL, 'en', Contact::class, 'contact')
        );
    }
}
