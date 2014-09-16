<?php
namespace OroCRM\Bundle\MarketingListBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class MarketingListSelectType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            [
                'autocomplete_alias' => 'marketing_lists',
                'create_form_route' => 'orocrm_marketing_list_create',
                'configs' => [
                    'placeholder' => 'orocrm.marketinglist.form.choose_marketing_list'
                ],
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'oro_entity_create_or_select_inline';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'orocrm_marketing_list_select';
    }
}
