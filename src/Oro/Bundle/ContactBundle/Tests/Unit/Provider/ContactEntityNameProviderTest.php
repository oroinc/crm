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
    /** @var ContactEntityNameProvider */
    protected $provider;

    /** @var ServiceLink */
    protected $nameFormatterLink;

    /** @var ServiceLink */
    protected $dqlNameFormatterLink;

    /** @var ContactNameFormatter */
    protected $contactNameFormatter;

    /** @var DQLNameFormatter */
    protected $dqlNameFormatter;

    protected function setUp(): void
    {
        $this->contactNameFormatter = $this
            ->getMockBuilder('Oro\Bundle\ContactBundle\Formatter\ContactNameFormatter')
            ->disableOriginalConstructor()->getMock();

        $this->dqlNameFormatter = $this
            ->getMockBuilder('Oro\Bundle\LocaleBundle\DQL\DQLNameFormatter')
            ->disableOriginalConstructor()->getMock();

        $this->nameFormatterLink = $this->createMock(ServiceLink::class);

        $this->dqlNameFormatterLink = $this->createMock(ServiceLink::class);

        $this->provider = new ContactEntityNameProvider($this->nameFormatterLink, $this->dqlNameFormatterLink);
    }

    /**
     * @dataProvider getNameDataProvider
     */
    public function testGetName($format, $locale, $entity, $expected)
    {
        if ($expected) {
            $this->contactNameFormatter->expects($this->once())->method('format')
                ->willReturn('Contact');

            $this->nameFormatterLink->expects($this->once())
                ->method('getService')
                ->willReturn($this->contactNameFormatter);
        }

        $result = $this->provider->getName($format, $locale, $entity);
        $this->assertEquals($expected, $result);
    }

    /**
     * @dataProvider getNameDQLDataProvider
     */
    public function testGetNameDQL($format, $locale, $className, $alias, $expected)
    {
        if ($expected) {
            $this->dqlNameFormatter->expects($this->once())->method('getFormattedNameDQL')
                ->willReturn('Contact');

            $this->dqlNameFormatterLink->expects($this->once())
                ->method('getService')
                ->willReturn($this->dqlNameFormatter);
        }

        $result = $this->provider->getNameDQL($format, $locale, $className, $alias);
        $this->assertEquals($expected, $result);
    }

    /**
     * @return array
     */
    public function getNameDataProvider()
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
                'entity' => $this->getEntity(),
                'expected' => false
            ],
            'correct data' => [
                'format' => EntityNameProviderInterface::FULL,
                'locale' => '',
                'entity' => $this->getEntity(),
                'expected' => 'Contact'
            ]
        ];
    }

    /**
     * @return array
     */
    public function getNameDQLDataProvider()
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

    /**
     * @return Contact
     */
    protected function getEntity()
    {
        return new Contact();
    }
}
