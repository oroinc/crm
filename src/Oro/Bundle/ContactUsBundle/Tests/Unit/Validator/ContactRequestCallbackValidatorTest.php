<?php

namespace Oro\Bundle\ContactUsBundle\Tests\Unit\Validator;

use Oro\Bundle\ContactUsBundle\Entity\ContactRequest;
use Oro\Bundle\ContactUsBundle\Validator\ContactRequestCallbackValidator;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class ContactRequestCallbackValidatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider validationDataProvider
     */
    public function testValidationCallback(
        ?string $phone,
        ?string $email,
        string $method,
        int $expectedViolationCount
    ): void {
        $request = new ContactRequest();
        $request->setPhone($phone);
        $request->setEmailAddress($email);
        $request->setPreferredContactMethod($method);

        $builder = $this->createMock(ConstraintViolationBuilderInterface::class);
        $builder->expects($this->exactly($expectedViolationCount))
            ->method('atPath')
            ->willReturnSelf();
        $builder->expects($this->exactly($expectedViolationCount))
            ->method('addViolation');
        $context = $this->createMock(ExecutionContextInterface::class);
        $context->expects($this->exactly($expectedViolationCount))
            ->method('buildViolation')
            ->willReturn($builder);

        ContactRequestCallbackValidator::validate($request, $context);
    }

    public function validationDataProvider(): array
    {
        return [
            'phone only required'                 => [
                'phone',
                null,
                ContactRequest::CONTACT_METHOD_PHONE,
                0
            ],
            'phone only required, error if empty' => [
                null,
                null,
                ContactRequest::CONTACT_METHOD_PHONE,
                1
            ],
            'email only required'                 => [
                null,
                'email',
                ContactRequest::CONTACT_METHOD_EMAIL,
                0
            ],
            'email only required, error if empty' => [
                null,
                null,
                ContactRequest::CONTACT_METHOD_EMAIL,
                1
            ],
            'both required'                       => [
                null,
                null,
                ContactRequest::CONTACT_METHOD_BOTH,
                2
            ],
            'both required, email given only'     => [
                null,
                'email',
                ContactRequest::CONTACT_METHOD_BOTH,
                1
            ],
            'both required, phone given only'     => [
                'phone',
                null,
                ContactRequest::CONTACT_METHOD_BOTH,
                1
            ],
            'both required, both given'           => [
                'phone',
                'email',
                ContactRequest::CONTACT_METHOD_BOTH,
                0
            ],
        ];
    }
}
