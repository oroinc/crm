<?php

namespace OroCRM\Bundle\CampaignBundle\Form\Type;

use Symfony\Component\Form\AbstractType;

class CodeType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'text';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'orocrm_campaign_code_type';
    }
}
