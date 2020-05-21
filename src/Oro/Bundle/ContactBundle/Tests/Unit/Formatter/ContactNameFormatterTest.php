<?php

namespace Oro\Bundle\ContactBundle\Tests\Unit\Formatter;

use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\ContactBundle\Entity\ContactEmail;
use Oro\Bundle\ContactBundle\Entity\ContactPhone;
use Oro\Bundle\ContactBundle\Formatter\ContactNameFormatter;
use Oro\Bundle\LocaleBundle\Formatter\NameFormatter;

class ContactNameFormatterTest extends \PHPUnit\Framework\TestCase
{
    /** @var NameFormatter */
    protected $nameFormatter;

    protected function setUp(): void
    {
        $this->nameFormatter = $this->getMockBuilder('Oro\Bundle\LocaleBundle\Formatter\NameFormatter')
            ->disableOriginalConstructor()
            ->getMock();

        $this->nameFormatter->expects($this->any())
            ->method('format')
            ->will($this->returnCallback(function (Contact $contact) {
                return trim(implode(' ', [$contact->getFirstName(), $contact->getLastName()]));
            }));
    }

    /**
     * @dataProvider formatDataProvider
     */
    public function testFormat(Contact $contact, $expectedResult)
    {
        $contactNameFormatter = new ContactNameFormatter($this->nameFormatter);
        $this->assertEquals($expectedResult, $contactNameFormatter->format($contact));
    }

    public function formatDataProvider()
    {
        return [
            'contact with all contact info' => [
                (new Contact())
                    ->setFirstName('first')
                    ->setLastName('last')
                    ->addEmail((new ContactEmail('em@example.com'))->setPrimary(true))
                    ->addPhone((new ContactPhone('542435'))->setPrimary(true)),
                'first last',
            ],
            'contact with empty name' => [
                (new Contact())
                    ->addEmail((new ContactEmail('em@example.com'))->setPrimary(true))
                    ->addPhone((new ContactPhone('542435'))->setPrimary(true)),
                'em@example.com',
            ],
            'contact with only phone' => [
                (new Contact())
                    ->addPhone((new ContactPhone('542435'))->setPrimary(true)),
                '542435',
            ],
            'contact with only email' => [
                (new Contact())
                    ->addEmail((new ContactEmail('em@example.com'))->setPrimary(true)),
                'em@example.com',
            ],
        ];
    }
}
