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
    const NAME = 'oro_sales_lead_to_opportunity';

    /** @var bool */
    protected $useFullContactForm = false;

    /**
     * {@inheritdoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['use_full_contact_form'] = $this->useFullContactForm;
    }

    /**
     * {@inheritdoc}
     */
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

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return self::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return OpportunityType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->getBlockPrefix();
    }
}
