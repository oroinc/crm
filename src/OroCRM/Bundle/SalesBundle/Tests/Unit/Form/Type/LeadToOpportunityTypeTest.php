<?php

namespace OroCRM\Bundle\SalesBundle\Tests\Unit\Form\Type;

use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormView;

use OroCRM\Bundle\ContactBundle\Entity\Contact;
use OroCRM\Bundle\SalesBundle\Entity\Opportunity;
use OroCRM\Bundle\SalesBundle\Form\Type\LeadToOpportunityType;

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

    public function testPreSetDataWithContact()
    {
        $form = $this->getMockBuilder('Symfony\Component\Form\Form')
                ->setMethods(['remove', 'add'])
                ->disableOriginalConstructor()
                ->getMock();
        $lead = $this->getMockBuilder('OroCRM\Bundle\SalesBundle\Entity\Lead')
                ->setMethods(['getContact'])
                ->disableOriginalConstructor()
                ->getMock();
        $lead
            ->expects($this->once())
            ->method('getContact')
            ->willReturn(new Contact());

        $form
            ->expects($this->never())
            ->method('remove')
            ->will($this->returnSelf());
        $form
            ->expects($this->never())
            ->method('add')
            ->will($this->returnSelf());

        $opportunity = new Opportunity();
        $opportunity->setLead($lead);
        $formEvent = new FormEvent($form, $opportunity);
        $this->type->onPreSetData($formEvent);

        $formView = new FormView();
        $this->type->finishView($formView, $form, []);
        $this->assertArraySubset(['use_full_contact_form' => false], $formView->vars);
    }

    public function testPreSetDataWithoutContact()
    {
        $form = $this->getMockBuilder('Symfony\Component\Form\Form')
                ->setMethods(['remove', 'add'])
                ->disableOriginalConstructor()
                ->getMock();
        $lead = $this->getMockBuilder('OroCRM\Bundle\SalesBundle\Entity\Lead')
                ->setMethods(['getContact'])
                ->disableOriginalConstructor()
                ->getMock();
        $lead
            ->expects($this->once())
            ->method('getContact')
            ->willReturn(null);

        $form
            ->expects($this->once())
            ->method('remove')
            ->will($this->returnSelf());
        $form
            ->expects($this->once())
            ->method('add')
            ->will($this->returnSelf());

        $opportunity = new Opportunity();
        $opportunity->setLead($lead);
        $formEvent = new FormEvent($form, $opportunity);
        $this->type->onPreSetData($formEvent);

        $formView = new FormView();
        $this->type->finishView($formView, $form, []);
        $this->assertArraySubset(['use_full_contact_form' => true], $formView->vars);
    }

    /**
     * @dataProvider contactFieldTypeDataProvider
     *
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
