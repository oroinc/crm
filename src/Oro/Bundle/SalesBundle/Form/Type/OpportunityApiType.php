<?php

namespace Oro\Bundle\SalesBundle\Form\Type;

use Oro\Bundle\SoapBundle\Form\EventListener\PatchSubscriber;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * The form type for Opportunity entity for API only.
 */
class OpportunityApiType extends OpportunityType
{
    const string NAME = 'oro_sales_opportunity_api';

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder->addEventSubscriber(new PatchSubscriber());
        if ($builder->has('status')) {
            $options = $builder->get('status')->getOptions();
            $options['choice_value'] = 'internalId';
            $builder->add('status', OpportunityStatusSelectType::class, $options);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'Oro\Bundle\SalesBundle\Entity\Opportunity',
                'csrf_token_id' => 'opportunity',
                'csrf_protection' => false,
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->getBlockPrefix();
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return self::NAME;
    }
}
