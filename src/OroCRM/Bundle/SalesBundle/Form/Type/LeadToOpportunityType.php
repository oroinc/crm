<?php

namespace OroCRM\Bundle\SalesBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use OroCRM\Bundle\SalesBundle\Entity\Opportunity;

class LeadToOpportunityType extends OpportunityType
{
    const BASE_NAME = 'orocrm_sales_lead_to_opportunity';

    protected $useFullContactForm = false;
    protected $name;

    /**
     * {@inheritdoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['use_full_contact_form'] = $this->useFullContactForm;
    }

    /**
     * @param bool $useFullContactForm
     */
    public function setUseFullContactForm($useFullContactForm)
    {
        $this->useFullContactForm = $useFullContactForm;
        $this->name = $useFullContactForm ? self::BASE_NAME . '_with_subform' : self::BASE_NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        parent::setDefaultOptions($resolver);

        $resolver->setDefaults(
            [
                'cascade_validation' => true
            ]
        );
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        if ($this->useFullContactForm) {
            $builder
                ->remove('contact')
                ->add('contact', 'orocrm_contact');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->getBlockPrefix();
    }
}
