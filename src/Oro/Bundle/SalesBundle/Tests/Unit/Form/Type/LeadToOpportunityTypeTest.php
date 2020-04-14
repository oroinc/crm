<?php

namespace Oro\Bundle\SalesBundle\Tests\Unit\Form\Type;

use Oro\Bundle\ChannelBundle\Form\Type\ChannelSelectType;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\ContactBundle\Form\Type\ContactSelectType;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\EntityExtendBundle\Cache\EnumTranslationCache;
use Oro\Bundle\EntityExtendBundle\Entity\AbstractEnumValue;
use Oro\Bundle\EntityExtendBundle\Entity\Repository\EnumValueRepository;
use Oro\Bundle\EntityExtendBundle\Form\Util\EnumTypeHelper;
use Oro\Bundle\EntityExtendBundle\Provider\EnumValueProvider;
use Oro\Bundle\EntityExtendBundle\Tests\Unit\Fixtures\TestEnumValue;
use Oro\Bundle\FormBundle\Form\Type\OroDateType;
use Oro\Bundle\FormBundle\Form\Type\OroMoneyType;
use Oro\Bundle\FormBundle\Form\Type\OroPercentType;
use Oro\Bundle\FormBundle\Form\Type\OroResizeableRichTextType;
use Oro\Bundle\SalesBundle\Entity\Opportunity;
use Oro\Bundle\SalesBundle\Form\Type\LeadToOpportunityType;
use Oro\Bundle\SalesBundle\Provider\ProbabilityProvider;
use Oro\Bundle\TranslationBundle\Form\Type\TranslatableEntityType;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormView;

class LeadToOpportunityTypeTest extends \PHPUnit\Framework\TestCase
{
    /** @var LeadToOpportunityType */
    protected $type;

    /**
     * Setup test env
     */
    protected function setUp(): void
    {
        $this->type = $this->getFormType(['in_progress']);
    }

    public function testPreSetDataWithContact()
    {
        $form = $this->getMockBuilder('Symfony\Component\Form\Form')
                ->setMethods(['remove', 'add'])
                ->disableOriginalConstructor()
                ->getMock();
        $lead = $this->getMockBuilder('Oro\Bundle\SalesBundle\Entity\Lead')
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
        $this->assertSame(false, $formView->vars['use_full_contact_form']);
    }

    public function testPreSetDataWithoutContact()
    {
        $form = $this->getMockBuilder('Symfony\Component\Form\Form')
                ->setMethods(['remove', 'add'])
                ->disableOriginalConstructor()
                ->getMock();
        $lead = $this->getMockBuilder('Oro\Bundle\SalesBundle\Entity\Lead')
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
        $this->assertSame(true, $formView->vars['use_full_contact_form']);
    }

    public function testBuildForm()
    {
        /** @var FormBuilder|\PHPUnit\Framework\MockObject\MockObject $builder */
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
                  'closeReason'  => TranslatableEntityType::class,
                  'contact' => ContactSelectType::class,
                  'customer' => 'oro_sales_b2bcustomer_with_channel_create_or_select',
                  'name'  => 'text',
                  'dataChannel'  => ChannelSelectType::class,
                  'closeDate'  => OroDateType::class,
                  'probability'  => OroPercentType::class,
                  'budgetAmount' => OroMoneyType::class,
                  'closeRevenue'  => OroMoneyType::class,
                  'customerNeed'  => OroResizeableRichTextType::class,
                  'proposedSolution'  => OroResizeableRichTextType::class,
                  'notes'  => OroResizeableRichTextType::class,
                  'status'  => 'oro_sales_opportunity_status_select',
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
        /** @var ConfigManager|\PHPUnit\Framework\MockObject\MockObject $configManager */
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
        /** @var EnumTranslationCache|\PHPUnit\Framework\MockObject\MockObject $cache */
        $cache = $this->createMock(EnumTranslationCache::class);
        $enumProvider = new EnumValueProvider($doctrineHelper, $cache);
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
