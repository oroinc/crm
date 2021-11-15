<?php

namespace Oro\Bundle\ContactBundle\Tests\Unit\Provider;

use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\ContactBundle\Formatter\ContactNameFormatter;
use Oro\Bundle\ContactBundle\Provider\ContactEntityNameProvider;
use Oro\Bundle\EntityBundle\Provider\EntityNameProviderInterface;
use Oro\Bundle\LocaleBundle\DQL\DQLNameFormatter;
use Oro\Component\DependencyInjection\ServiceLink;

class ContactEntityNameProviderTest extends \PHPUnit\Framework\TestCase
{
    /** @var ContactNameFormatter|\PHPUnit\Framework\MockObject\MockObject */
    private $contactNameFormatter;

    /** @var DQLNameFormatter|\PHPUnit\Framework\MockObject\MockObject */
    private $dqlNameFormatter;

    /** @var ContactEntityNameProvider */
    private $provider;

    protected function setUp(): void
    {
        $this->contactNameFormatter = $this->createMock(ContactNameFormatter::class);
        $this->dqlNameFormatter = $this->createMock(DQLNameFormatter::class);

        $nameFormatterLink = $this->createMock(ServiceLink::class);
        $nameFormatterLink->expects($this->any())
            ->method('getService')
            ->willReturn($this->contactNameFormatter);

        $dqlNameFormatterLink = $this->createMock(ServiceLink::class);
        $dqlNameFormatterLink->expects($this->any())
            ->method('getService')
            ->willReturn($this->dqlNameFormatter);

        $this->provider = new ContactEntityNameProvider($nameFormatterLink, $dqlNameFormatterLink);
    }

    /**
     * @dataProvider getNameDataProvider
     */
    public function testGetName(string $format, ?string $locale, object $entity, string|false $expected)
    {
        if ($expected) {
            $this->contactNameFormatter->expects($this->once())
                ->method('format')
                ->willReturn('Contact');
        }

        $result = $this->provider->getName($format, $locale, $entity);
        $this->assertSame($expected, $result);
    }

    /**
     * @dataProvider getNameDQLDataProvider
     */
    public function testGetNameDQL(
        string $format,
        ?string $locale,
        string $className,
        string $alias,
        string|false $expected
    ) {
        if ($expected) {
            $this->dqlNameFormatter->expects($this->once())
                ->method('getFormattedNameDQL')
                ->willReturn('Contact');
        }

        $result = $this->provider->getNameDQL($format, $locale, $className, $alias);
        $this->assertSame($expected, $result);
    }

    public function getNameDataProvider(): array
    {
        return [
            'test unsupported class' => [
                'format' => '',
                'locale' => null,
                'entity' => new \stdClass(),
                'expected' => false
            ],
            'test unsupported format' => [
                'format' => '',
                'locale' => null,
                'entity' => new Contact(),
                'expected' => false
            ],
            'correct data' => [
                'format' => EntityNameProviderInterface::FULL,
                'locale' => '',
                'entity' => new Contact(),
                'expected' => 'Contact'
            ]
        ];
    }

    public function getNameDQLDataProvider(): array
    {
        return [
            'test unsupported class Name' => [
                'format' => '',
                'locale' => null,
                'className' => '',
                'alias' => '',
                'expected' => false
            ],
            'test unsupported format' => [
                'format' => '',
                'locale' => null,
                'className' => Contact::class,
                'alias' => '',
                'expected' => false
            ],
            'correct data' => [
                'format' => EntityNameProviderInterface::FULL,
                'locale' => null,
                'className' => Contact::class,
                'alias' => 'test',
                'expected' => 'COALESCE(' .
                              'NULLIF(Contact, \'\'), ' .
                              'CAST((SELECT test_emails.email' .
                              ' FROM Oro\Bundle\ContactBundle\Entity\Contact test_emails_base' .
                              ' LEFT JOIN test_emails_base.emails test_emails' .
                              ' WHERE test_emails.primary = true AND test_emails_base = test) ' .
                              'AS string), ' .
                              'CAST((SELECT test_phones.phone' .
                              ' FROM Oro\Bundle\ContactBundle\Entity\Contact test_phones_base' .
                              ' LEFT JOIN test_phones_base.phones test_phones' .
                              ' WHERE test_phones.primary = true AND test_phones_base = test) ' .
                              'AS string))'
            ]
        ];
    }
}
