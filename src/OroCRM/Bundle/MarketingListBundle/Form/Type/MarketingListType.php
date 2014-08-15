<?php

namespace OroCRM\Bundle\MarketingListBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

//use Oro\Bundle\QueryDesignerBundle\Form\Type\AbstractQueryDesignerType;

class MarketingListType extends AbstractType //AbstractQueryDesignerType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'text', ['required' => true])
            ->add('entity', 'oro_entity_choice', ['required' => true])
            ->add(
                'type',
                'entity',
                array(
                    'class'       => 'OroCRMMarketingListBundle:MarketingListType',
                    'property'    => 'label',
                    'required'    => true,
                    'empty_value' => 'orocrm.marketinglist.form.choose_marketing_list_type'
                )
            )
            ->add('description', 'textarea', ['required' => false]);

        //parent::buildForm($builder, $options);
    }

    /**
     * Gets the default options for this type.
     *
     * @return array
     */
    public function getDefaultOptions()
    {
        return [];
//        return [
//            'column_column_choice_type' => 'hidden',
//            'filter_column_choice_type' => 'oro_entity_field_select'
//        ];
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $options = array_merge(
            $this->getDefaultOptions(),
            [
                'data_class'         => 'OroCRM\Bundle\MarketingListBundle\Entity\MarketingList',
                'intention'          => 'marketing_list',
                'cascade_validation' => true
            ]
        );

        $resolver->setDefaults($options);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'orocrm_marketing_list';
    }
}
