<?php

namespace Oro\Bundle\ContactBundle\Tests\Unit\Validator\Constraints;

use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\ContactBundle\Entity\ContactEmail;
use Oro\Bundle\ContactBundle\Entity\ContactPhone;
use Oro\Bundle\ContactBundle\Validator\Constraints\HasContactInformation;
use Oro\Bundle\ContactBundle\Validator\Constraints\HasContactInformationValidator;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

class HasContactInformationValidatorTest extends ConstraintValidatorTestCase
{
    protected function createValidator()
    {
        $translator = $this->createMock(TranslatorInterface::class);
        $translator->expects($this->any())
            ->method('trans')
            ->willReturnArgument(0);

        return new HasContactInformationValidator($translator);
    }

    /**
     * @dataProvider validValuesProvider
     */
    public function testValidValues(?Contact $value)
    {
        $this->validator->validate($value, new HasContactInformation());

        $this->assertNoViolation();
    }

    public function validValuesProvider(): array
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

    public function testInvalidValue()
    {
        $value = new Contact();

        $constraint = new HasContactInformation();
        $this->validator->validate($value, $constraint);

        $this->buildViolation('oro.contact.validators.contact.has_information')
            ->assertRaised();
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
