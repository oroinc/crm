<?php

namespace Oro\Bundle\SalesBundle\Form\Type;

use Oro\Bundle\ContactBundle\Form\Type\ContactType;
use Oro\Bundle\SalesBundle\Entity\Opportunity;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Validator\Constraints\Valid;

class LeadToOpportunityType extends AbstractType
{
    public const NAME = 'oro_sales_lead_to_opportunity';

    /** @var bool */
    protected $useFullContactForm = false;

    #[\Override]
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['use_full_contact_form'] = $this->useFullContactForm;
    }

    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, array($this, 'onPreSetData'));
    }

    public function onPreSetData(FormEvent $event)
    {
        $form = $event->getForm();
        $entity = $event->getData();
        if ($entity instanceof Opportunity && !$entity->getLead()->getContact()) {
            $form->remove('contact');
            $form->add(
                'contact',
                ContactType::class,
                [
                    'constraints' => new Valid()
                ]
            );
            $this->useFullContactForm = true;
        }
    }

    #[\Override]
    public function getBlockPrefix(): string
    {
        return self::NAME;
    }

    #[\Override]
    public function getParent(): ?string
    {
        return OpportunityType::class;
    }

    public function getName()
    {
        return $this->getBlockPrefix();
    }
}
