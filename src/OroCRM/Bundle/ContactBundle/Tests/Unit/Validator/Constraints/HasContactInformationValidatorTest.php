<?php

namespace OroCRM\Bundle\ContactBundle\Tests\Unit\Validator\Constraints;

use Symfony\Component\Validator\ExecutionContextInterface;

use OroCRM\Bundle\ContactBundle\Entity\Contact;
use OroCRM\Bundle\ContactBundle\Entity\ContactEmail;
use OroCRM\Bundle\ContactBundle\Entity\ContactPhone;
use OroCRM\Bundle\ContactBundle\Validator\Constraints\HasContactInformation;
use OroCRM\Bundle\ContactBundle\Validator\Constraints\HasContactInformationValidator;

class HasContactInformationValidatorTest extends \PHPUnit_Framework_TestCase
{
    /** @var ExecutionContextInterface */
    protected $context;

    /** @var HasContactInformationValidator */
    protected $validator;

    public function setUp()
    {
        $translator = $translator = $this->getMock('Symfony\Component\Translation\TranslatorInterface');
        $translator
            ->expects($this->any())
            ->method('trans')
            ->will($this->returnCallback(function ($id) {
                return $id;
            }));

        $this->context = $this->getMock('Symfony\Component\Validator\Context\ExecutionContextInterface');
        $this->validator = new HasContactInformationValidator($translator);
        $this->validator->initialize($this->context);
    }

    /**
     * @dataProvider validValuesProvider
     */
    public function testValidValues($value)
    {
        $this->context->expects($this->never())
            ->method('addViolation');

        $this->validator->validate($value, new HasContactInformation());
    }

    public function validValuesProvider()
    {
        return [
            [
                null
            ],
            [
                (new Contact())
                    ->setFirstName('first'),
            ],
            [
                (new Contact())
                    ->setLastName('last'),
            ],
            [
                (new Contact())
                    ->addEmail(new ContactEmail('contact@example.com')),
            ],
            [
                (new Contact())
                    ->addPhone(new ContactPhone('phone@example.com')),
            ]
        ];
    }

    /**
     * @dataProvider invalidValuesProvider
     */
    public function testInvalidValues($value)
    {
        $this->context->expects($this->once())
            ->method('addViolation')
            ->with(
                'At least one of the fields %fields% must be defined.',
                [
                    '%fields%' => 'orocrm.contact.first_name.label, orocrm.contact.last_name.label, ' .
                                  'orocrm.contact.emails.label or orocrm.contact.phones.label'
                ]
            );

        $this->validator->validate($value, new HasContactInformation());
    }

    public function invalidValuesProvider()
    {
        return [
            [
                new Contact(),
            ],
        ];
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Validator expects $value to be instance of "OroCRM\Bundle\ContactBundle\Entity\Contact"
     */
    public function testInvalidArgument()
    {
        $this->validator->validate(new ContactEmail(), new HasContactInformation());
    }
}
