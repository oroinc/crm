<?php

namespace OroCRM\Bundle\MarketingListBundle\Tests\Unit\Validator;

use OroCRM\Bundle\MarketingListBundle\Validator\Constraints\ContactInformationColumnConstraint;
use OroCRM\Bundle\MarketingListBundle\Validator\ContactInformationColumnValidator;

class ContactInformationColumnValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $contactInformationFieldHelper;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $context;

    /**
     * @var ContactInformationColumnValidator
     */
    protected $validator;

    protected function setUp()
    {
        $this->contactInformationFieldHelper = $this
            ->getMockBuilder('OroCRM\Bundle\MarketingListBundle\Model\ContactInformationFieldHelper')
            ->disableOriginalConstructor()
            ->getMock();

        $this->context = $this->getMockBuilder('Symfony\Component\Validator\ExecutionContextInterface')
            ->getMock();

        $this->validator = new ContactInformationColumnValidator($this->contactInformationFieldHelper);
        $this->validator->initialize($this->context);
    }

    /**
     * @expectedException \Symfony\Component\Validator\Exception\UnexpectedTypeException
     * @expectedExceptionMessage Expected argument of type "string", "array" given
     */
    public function testValidateFieldException()
    {
        $constraint = new ContactInformationColumnConstraint();
        $constraint->field = ['test'];

        $value = $this->getMockForAbstractClass('Oro\Bundle\QueryDesignerBundle\Model\AbstractQueryDesigner');
        $this->validator->validate($value, $constraint);
    }

    /**
     * @expectedException \Symfony\Component\Validator\Exception\UnexpectedTypeException
     * @expectedExceptionMessage Expected argument of type "AbstractQueryDesigner", "string" given
     */
    public function testValidateValueException()
    {
        $constraint = new ContactInformationColumnConstraint();

        $value = 'test';
        $this->validator->validate($value, $constraint);
    }

    public function testValidateValid()
    {
        $constraint = new ContactInformationColumnConstraint();
        $value = $this->getMockForAbstractClass('Oro\Bundle\QueryDesignerBundle\Model\AbstractQueryDesigner');

        $this->contactInformationFieldHelper->expects($this->once())
            ->method('getQueryContactInformationColumns')
            ->with($value)
            ->will($this->returnValue(['email' => ['testField']]));

        $this->context->expects($this->never())
            ->method($this->anything());

        $this->validator->validate($value, $constraint);
    }

    /**
     * @dataProvider validatorDataProvider
     * @param string $type
     * @param array $contactInformationFields
     */
    public function testValidateInvalidClass($type, array $contactInformationFields)
    {
        $constraint = new ContactInformationColumnConstraint();
        $constraint->type = $type;
        $value = $this->getMockForAbstractClass('Oro\Bundle\QueryDesignerBundle\Model\AbstractQueryDesigner');

        $this->contactInformationFieldHelper->expects($this->once())
            ->method('getQueryContactInformationColumns')
            ->with($value)
            ->will($this->returnValue($contactInformationFields));

        $parameters = [];
        if ($type) {
            $message = $constraint->typeMessage;
            $parameters['%type%'] = $type;
        } else {
            $message = $constraint->message;
        }

        $this->context->expects($this->once())
            ->method('addViolation')
            ->with($message, $parameters);

        $this->validator->validate($value, $constraint);
    }

    /**
     * @return array
     */
    public function validatorDataProvider()
    {
        return [
            'no type' => [null, []],
            'type no fields' => ['email', []],
            'no typed fields' => ['email', ['phone' => ['test']]]
        ];
    }

    /**
     * @dataProvider validatorDataProvider
     * @param string $type
     * @param array $contactInformationFields
     */
    public function testValidateInvalidField($type, array $contactInformationFields)
    {
        $constraint = new ContactInformationColumnConstraint();
        $constraint->field = 'test';
        $constraint->type = $type;
        $value = new \stdClass();
        $value->test = $this->getMockForAbstractClass('Oro\Bundle\QueryDesignerBundle\Model\AbstractQueryDesigner');

        $this->contactInformationFieldHelper->expects($this->once())
            ->method('getQueryContactInformationColumns')
            ->with($value->test)
            ->will($this->returnValue($contactInformationFields));

        $parameters = [];
        if ($type) {
            $message = $constraint->typeMessage;
            $parameters['%type%'] = $type;
        } else {
            $message = $constraint->message;
        }

        $this->context->expects($this->once())
            ->method('addViolationAt')
            ->with($constraint->field, $message, $parameters);

        $this->validator->validate($value, $constraint);
    }
}
