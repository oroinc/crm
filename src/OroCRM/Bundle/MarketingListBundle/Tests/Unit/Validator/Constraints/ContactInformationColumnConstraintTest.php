<?php

namespace OroCRM\Bundle\MarketingListBundle\Tests\Unit\Validator\Constraints;

use OroCRM\Bundle\MarketingListBundle\Validator\Constraints\ContactInformationColumnConstraint;

class ContactInformationColumnConstraintTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ContactInformationColumnConstraint
     */
    protected $constraint;

    protected function setUp()
    {
        $this->constraint = new ContactInformationColumnConstraint();
    }

    public function testGetTargets()
    {
        $this->assertEquals(
            array(
                ContactInformationColumnConstraint::CLASS_CONSTRAINT,
                ContactInformationColumnConstraint::PROPERTY_CONSTRAINT
            ),
            $this->constraint->getTargets()
        );
    }

    public function testGetDefaultOption()
    {
        $this->assertEquals('field', $this->constraint->getDefaultOption());
    }

    public function testValidatedBy()
    {
        $this->assertNotEmpty($this->constraint->validatedBy());
    }
}
