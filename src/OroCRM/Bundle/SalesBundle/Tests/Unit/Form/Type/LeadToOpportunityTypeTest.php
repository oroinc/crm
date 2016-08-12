<?php

namespace OroCRM\Bundle\SalesBundle\Tests\Unit\Form\Type;

use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormView;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\EntityExtendBundle\Entity\AbstractEnumValue;
use Oro\Bundle\EntityExtendBundle\Entity\Repository\EnumValueRepository;
use Oro\Bundle\EntityExtendBundle\Form\Util\EnumTypeHelper;
use Oro\Bundle\EntityExtendBundle\Provider\EnumValueProvider;
use Oro\Bundle\EntityExtendBundle\Tests\Unit\Fixtures\TestEnumValue;

use OroCRM\Bundle\ContactBundle\Entity\Contact;
use OroCRM\Bundle\SalesBundle\Entity\Opportunity;
use OroCRM\Bundle\SalesBundle\Form\Type\LeadToOpportunityType;
use OroCRM\Bundle\SalesBundle\Provider\ProbabilityProvider;

class LeadToOpportunityTypeTest extends \PHPUnit_Framework_TestCase
{
    /** @var LeadToOpportunityType */
    protected $type;

    /**
     * Setup test env
     */
    protected function setUp()
    {
        $this->type = $this->getFormType(['in_progress']);
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
        /** @var FormBuilder|\PHPUnit_Framework_MockObject_MockObject $builder */
        $builder = $this->getMockBuilder('Symfony\Component\Form\FormBuilder')
            ->disableOriginalConstructor()
            ->setMethods(['addEventListener'])
            ->getMock();
        $builder
            ->expects($this->exactly(1))
            ->method('addEventListener')
            ->will($this->returnSelf());

        $this->type->buildForm($builder, []);
    }

    public function contactFieldTypeDataProvider()
    {
        return [
          [
              'fields' => [
                  'closeReason'  => 'translatable_entity',
                  'contact'  => 'orocrm_contact_select',
                  'customer' => 'orocrm_sales_b2bcustomer_with_channel_create_or_select',
                  'name'  => 'text',
                  'dataChannel'  => 'orocrm_channel_select_type',
                  'closeDate'  => 'oro_date',
                  'probability'  => 'oro_percent',
                  'budgetAmount' => 'oro_money',
                  'closeRevenue'  => 'oro_money',
                  'customerNeed'  => 'oro_resizeable_rich_text',
                  'proposedSolution'  => 'oro_resizeable_rich_text',
                  'notes'  => 'oro_resizeable_rich_text',
                  'status'  => 'orocrm_sales_opportunity_status_select',
              ]
          ]
        ];
    }

    /**
     * @return array
     */
    protected function getDefaultProbabilities()
    {
        return [
            'identification_alignment' => 0.3,
            'needs_analysis' => 0.2,
            'solution_development' => 0.5,
            'negotiation' => 0.8,
            'in_progress' => 0.1,
            'won' => 1.0,
            'lost' => 0.0,
        ];
    }

    /**
     * @param AbstractEnumValue[] $defaultStatuses
     *
     * @return LeadToOpportunityType
     */
    protected function getFormType(array $defaultStatuses = [])
    {
        /** @var ConfigManager|\PHPUnit_Framework_MockObject_MockObject $configManager */
        $configManager = $this->getMockBuilder(ConfigManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $configManager
            ->expects($this->any())
            ->method('get')
            ->willReturn($this->getDefaultProbabilities());

        $probabilityProvider = new ProbabilityProvider($configManager);

        $defaultStatuses = array_map(
            function ($id) {
                return $this->getOpportunityStatus($id);
            },
            $defaultStatuses
        );

        $doctrineHelper = $this->getDoctrineHelperMock($defaultStatuses);
        $enumProvider = new EnumValueProvider($doctrineHelper);
        $helper = $this->getEnumTypeHelperMock();

        return new LeadToOpportunityType($probabilityProvider, $enumProvider, $helper);
    }

    /**
     * @param AbstractEnumValue[] $defaultValues
     *
     * @return DoctrineHelper
     */
    protected function getDoctrineHelperMock(array $defaultValues)
    {
        $repo = $this->getMockBuilder(EnumValueRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $repo
            ->expects($this->any())
            ->method('getDefaultValues')
            ->willReturn($defaultValues);

        $doctrine = $this->getMockBuilder(DoctrineHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $doctrine->expects($this->any())
            ->method('getEntityRepository')
            ->willReturn($repo);

        /** @var DoctrineHelper $doctrine */
        return $doctrine;
    }

    /**
     * @return EnumTypeHelper
     */
    protected function getEnumTypeHelperMock()
    {
        $helper = $this->getMockBuilder(EnumTypeHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $helper
            ->expects($this->any())
            ->method('getEnumCode')
            ->willReturn('opportunity_status');

        /** @var EnumTypeHelper $helper */
        return $helper;
    }

    /**
     * @param string $id
     *
     * @return AbstractEnumValue
     */
    protected function getOpportunityStatus($id)
    {
        return new TestEnumValue($id, $id);
    }
}
