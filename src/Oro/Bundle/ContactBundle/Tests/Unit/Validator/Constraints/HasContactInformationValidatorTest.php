<?php

namespace Oro\Bundle\ContactBundle\Tests\Unit\Validator\Constraints;

use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\ContactBundle\Entity\ContactEmail;
use Oro\Bundle\ContactBundle\Entity\ContactPhone;
use Oro\Bundle\ContactBundle\Validator\Constraints\HasContactInformation;
use Oro\Bundle\ContactBundle\Validator\Constraints\HasContactInformationValidator;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

class HasContactInformationValidatorTest extends ConstraintValidatorTestCase
{
    /**
     * {@inheritDoc}
     */
    protected function createValidator(): HasContactInformationValidator
    {
        return new HasContactInformationValidator();
    }

    public function testInvalidConstraintType()
    {
        $this->expectException(UnexpectedTypeException::class);
        $this->expectExceptionMessage(sprintf(
            'Expected argument of type "%s", "%s" given',
            HasContactInformation::class,
            NotBlank::class
        ));

        $this->validator->validate(new Contact(), new NotBlank());
    }

    public function testNotContactEntity()
    {
        $this->expectException(UnexpectedTypeException::class);
        $this->expectExceptionMessage(sprintf('Expected argument of type "%s", "stdClass" given', Contact::class));

        $this->validator->validate(new \stdClass(), new HasContactInformation());
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

    public function testContactWithoutContactInformation()
    {
        $value = new Contact();

        $constraint = new HasContactInformation();
        $this->validator->validate($value, $constraint);

        $this->buildViolation('oro.contact.validators.contact.has_information')
            ->assertRaised();
    }
}
