<?php

namespace OroCRM\Bundle\SalesBundle\Form\Type;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use OroCRM\Bundle\SalesBundle\Entity\Opportunity;

class LeadToOpportunityType extends OpportunityType
{
    const NAME = 'orocrm_sales_lead_to_opportunity';
    const CONTACT_FORM_ID = 'orocrm_contact';

    protected $contactAsSubForm = false;

    /**
     * {@inheritdoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['contact_as_subform'] = $this->contactAsSubForm;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        parent::setDefaultOptions($resolver);

        $resolver->setDefaults(
            [
                'convert_lead_to_opportunity' => true
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function addListeners(FormBuilderInterface $builder)
    {
        parent::addListeners($builder);

        $contactAsSubForm = &$this->contactAsSubForm;

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use (&$contactAsSubForm) {
                $opportunity = $event->getData();
                if ($opportunity instanceof Opportunity && !$opportunity->getContact()->getId()) {
                    $event->getForm()
                        ->remove('contact')
                        ->add('contact', self::CONTACT_FORM_ID);
                    $contactAsSubForm = true;
                }
            }
        );
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
    public function getName()
    {
        return $this->getBlockPrefix();
    }
}
