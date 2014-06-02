<?php

namespace OroCRM\Bundle\CampaignBundle\Form\Type;

use Symfony\Component\Form\AbstractType;

class CodeType extends AbstractType
{
    public function getParent()
    {
        return 'text';
    }

    public function getName()
    {
        return 'orocrm_campaign_code_type';
    }
}
