<?php

namespace OroCRM\Bundle\CampaignBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CampaignSelectType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            [
                'autocomplete_alias' => 'campaigns',
                'create_form_route' => 'orocrm_campaign_create',
                'configs' => [
                    'placeholder' => 'orocrm.campaign.form.choose_campaign'
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
        return 'orocrm_campaign_select';
    }
}
