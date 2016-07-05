<?php

namespace OroCRM\Bundle\SalesBundle\Tests\Unit\Form\Type;

use OroCRM\Bundle\SalesBundle\Form\Type\LeadToOpportunityType;

use Symfony\Component\Form\FormBuilder;

class LeadToOpportunityTypeTest extends \PHPUnit_Framework_TestCase
{
    /** @var LeadToOpportunityType */
    protected $type;

    /**
     * Setup test env
     */
    protected function setUp()
    {
        $this->type = new LeadToOpportunityType();
    }

    /**
     * @dataProvider formNameDataProvider
     *
     * @param bool $useFullContactForm
     * @param string $formTypeName
     */
    public function testGetName($useFullContactForm, $formTypeName)
    {
        $this->type->setUseFullContactForm($useFullContactForm);
        $this->assertSame($this->type->getName(), $formTypeName);
    }

    public function formNameDataProvider()
    {
        return [
            [
                'use_full_contact_form' => true,
                'name' => 'orocrm_sales_lead_to_opportunity_with_subform'
            ],
            [
                'use_full_contact_form' => false,
                'name' => 'orocrm_sales_lead_to_opportunity'
            ]
        ];
    }

    /**
     * @dataProvider contactFieldTypeDataProvider
     *
     * @param bool $useFullContactForm
     * @param int $addMethodCallCount
     */
    public function testBuildForm($useFullContactForm, $addMethodCallCount)
    {
        /**
         * @var FormBuilder $builder
         */
        $builder = $this->getMockBuilder('Symfony\Component\Form\FormBuilder')
            ->disableOriginalConstructor()
            ->setMethods(['add', 'remove', 'addEventListener'])
            ->getMock();
        $this->type->setUseFullContactForm($useFullContactForm);
        $builder->expects($this->once())->method('addEventListener')->will($this->returnSelf());
        $builder->expects($this->exactly($addMethodCallCount))->method('add')->will($this->returnSelf());

        if ($useFullContactForm) {
            $builder
                ->expects($this->once())
                ->method('remove')
                ->with('contact');
            $builder
                ->expects($this->at(15))
                ->method('add')
                ->with('contact', 'orocrm_contact')
                ->will($this->returnSelf());
        }

        $this->type->buildForm($builder, []);
    }

    public function contactFieldTypeDataProvider()
    {
        return [
          [
              'use_full_contact_form' => true,
              'add_call_count' => 14
          ],
          [
              'use_full_contact_form' => false,
              'add_call_count' => 13
          ]
        ];
    }
}
