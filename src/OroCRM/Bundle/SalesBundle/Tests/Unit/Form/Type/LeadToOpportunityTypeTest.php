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

    public function testPreSetData()
    {
        $this->markTestSkipped("Not implemented yet !");
    }

    /**
     * @dataProvider contactFieldTypeDataProvider
     *
     * @param bool $useFullContactForm
     * @param array $fields
     */
    public function testBuildForm(array $fields)
    {
        /**
         * @var FormBuilder $builder
         */
        $builder = $this->getMockBuilder('Symfony\Component\Form\FormBuilder')
            ->disableOriginalConstructor()
            ->setMethods(['add', 'remove', 'addEventListener'])
            ->getMock();
        $builder
            ->expects($this->exactly(2))
            ->method('addEventListener')
            ->will($this->returnSelf());

        $counter = 0;
        foreach ($fields as $fieldName => $formType) {
            $builder
                ->expects($this->at($counter))
                ->method('add')
                ->with($fieldName, $formType)
                ->will($this->returnSelf());
            $counter++;
        }

        $this->type->buildForm($builder, []);
    }

    public function contactFieldTypeDataProvider()
    {
        return [
          [
              'fields' => [
                  'closeReason'  => 'translatable_entity',
                  'contact'  => 'orocrm_contact_select',
                  'customer' => 'orocrm_sales_b2bcustomer_with_channel_select',
                  'name'  => 'text',
                  'dataChannel'  => 'orocrm_channel_select_type',
                  'closeDate'  => 'oro_date',
                  'probability'  => 'oro_percent',
                  'budgetAmount' => 'oro_money',
                  'closeRevenue'  => 'oro_money',
                  'customerNeed'  => 'oro_resizeable_rich_text',
                  'proposedSolution'  => 'oro_resizeable_rich_text',
                  'notes'  => 'oro_resizeable_rich_text',
                  'status'  => 'oro_enum_select',
              ]
          ]
        ];
    }
}
