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
     * @param array $fields
     */
    public function testBuildForm($useFullContactForm, array $fields)
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

        $counter = 0;
        foreach ($fields as $fieldName => $formType) {
            $builder
                ->expects($this->at($counter))
                ->method('add')
                ->with($fieldName, $formType)
                ->will($this->returnSelf());
            $counter++;
        }

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
          ],
          [
              'use_full_contact_form' => false,
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
