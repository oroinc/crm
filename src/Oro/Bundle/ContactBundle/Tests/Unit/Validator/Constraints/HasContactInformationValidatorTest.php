<?php

namespace Oro\Bundle\ContactBundle\Tests\Unit\Validator\Constraints;

use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\ContactBundle\Entity\ContactEmail;
use Oro\Bundle\ContactBundle\Entity\ContactPhone;
use Oro\Bundle\ContactBundle\Validator\Constraints\HasContactInformation;
use Oro\Bundle\ContactBundle\Validator\Constraints\HasContactInformationValidator;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class HasContactInformationValidatorTest extends \PHPUnit\Framework\TestCase
{
    /** @var ExecutionContextInterface */
    protected $context;

    /** @var HasContactInformationValidator */
    protected $validator;

    protected function setUp(): void
    {
        $translator = $translator = $this->createMock('Symfony\Contracts\Translation\TranslatorInterface');
        $translator
            ->expects($this->any())
            ->method('trans')
            ->will($this->returnCallback(function ($id) {
                return $id;
            }));

        $this->context = $this->createMock(ExecutionContextInterface::class);
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
                'oro.contact.validators.contact.has_information'
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

    public function testInvalidArgument()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Validator expects $value to be instance of "Oro\Bundle\ContactBundle\Entity\Contact"'
        );

        $this->validator->validate(new ContactEmail(), new HasContactInformation());
    }
}
