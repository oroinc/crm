<?php

namespace OroCRM\Bundle\ContactBundle\Tests\Unit\Formatter;

use Oro\Bundle\LocaleBundle\Formatter\NameFormatter;

use OroCRM\Bundle\ContactBundle\Entity\Contact;
use OroCRM\Bundle\ContactBundle\Entity\ContactEmail;
use OroCRM\Bundle\ContactBundle\Entity\ContactPhone;
use OroCRM\Bundle\ContactBundle\Formatter\ContactNameFormatter;

class ContactNameFormatterTest extends \PHPUnit_Framework_TestCase
{
    /** @var NameFormatter */
    protected $nameFormatter;

    public function setUp()
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
                '542435',
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
