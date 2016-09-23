<?php

namespace Oro\Bundle\CampaignBundle\Form\Type;

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
                'create_form_route' => 'oro_campaign_create',
                'configs' => [
                    'placeholder' => 'oro.campaign.form.choose_campaign'
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
        return $this->getBlockPrefix();
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'oro_campaign_select';
    }
}
