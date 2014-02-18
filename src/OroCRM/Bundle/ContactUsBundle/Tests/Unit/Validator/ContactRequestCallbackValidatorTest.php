<?php

namespace OroCRM\Bundle\ContactUsBundle\Tests\Unit\Validator;

use OroCRM\Bundle\ContactUsBundle\Entity\ContactRequest;
use OroCRM\Bundle\ContactUsBundle\Validator\ContactRequestCallbackValidator;

class ContactRequestCallbackValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider validationDataProvider
     *
     * @param mixed  $phone
     * @param mixed  $email
     * @param string $method
     * @param int    $expectedViolationCount
     */
    public function testValidationCallback($phone, $email, $method, $expectedViolationCount)
    {
        $request = new ContactRequest();
        $request->setPhone($phone);
        $request->setEmailAddress($email);
        $request->setPreferredContactMethod($method);

        $context = $this->getMockBuilder('Symfony\Component\Validator\ExecutionContext')
            ->disableOriginalConstructor()->getMock();
        $context->expects($this->exactly($expectedViolationCount))->method('addViolationAt');
        ContactRequestCallbackValidator::validate($request, $context);
    }

    /**
     * @return array
     */
    public function validationDataProvider()
    {
        return [
            'phone only required'                 => [
                uniqid('phone'),
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
                uniqid('email'),
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
                uniqid('email'),
                ContactRequest::CONTACT_METHOD_BOTH,
                1
            ],
            'both required, phone given only'     => [
                uniqid('phone'),
                null,
                ContactRequest::CONTACT_METHOD_BOTH,
                1
            ],
            'both required, both given'           => [
                uniqid('phone'),
                uniqid('email'),
                ContactRequest::CONTACT_METHOD_BOTH,
                0
            ],
        ];
    }
}
