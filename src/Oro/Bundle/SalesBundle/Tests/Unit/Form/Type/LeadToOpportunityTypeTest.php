<?php

namespace Oro\Bundle\SalesBundle\Tests\Unit\Form\Type;

use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\SalesBundle\Entity\Lead;
use Oro\Bundle\SalesBundle\Entity\Opportunity;
use Oro\Bundle\SalesBundle\Form\Type\LeadToOpportunityType;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormView;

class LeadToOpportunityTypeTest extends \PHPUnit\Framework\TestCase
{
    /** @var LeadToOpportunityType */
    private $type;

    protected function setUp(): void
    {
        $this->type = new LeadToOpportunityType();
    }

    public function testPreSetDataWithContact()
    {
        $form = $this->createMock(Form::class);
        $lead = $this->createMock(Lead::class);
        $lead->expects($this->once())
            ->method('getContact')
            ->willReturn(new Contact());

        $form->expects($this->never())
            ->method('remove')
            ->willReturnSelf();
        $form->expects($this->never())
            ->method('add')
            ->willReturnSelf();

        $opportunity = new Opportunity();
        $opportunity->setLead($lead);
        $formEvent = new FormEvent($form, $opportunity);
        $this->type->onPreSetData($formEvent);

        $formView = new FormView();
        $this->type->finishView($formView, $form, []);
        $this->assertFalse($formView->vars['use_full_contact_form']);
    }

    public function testPreSetDataWithoutContact()
    {
        $form = $this->createMock(Form::class);
        $lead = $this->createMock(Lead::class);
        $lead->expects($this->once())
            ->method('getContact')
            ->willReturn(null);

        $form->expects($this->once())
            ->method('remove')
            ->willReturnSelf();
        $form->expects($this->once())
            ->method('add')
            ->willReturnSelf();

        $opportunity = new Opportunity();
        $opportunity->setLead($lead);
        $formEvent = new FormEvent($form, $opportunity);
        $this->type->onPreSetData($formEvent);

        $formView = new FormView();
        $this->type->finishView($formView, $form, []);
        $this->assertTrue($formView->vars['use_full_contact_form']);
    }

    public function testBuildForm()
    {
        $builder = $this->createMock(FormBuilder::class);
        $builder->expects($this->once())
            ->method('addEventListener')
            ->willReturnSelf();

        $this->type->buildForm($builder, []);
    }
}
