<?php

namespace OroCRM\Bundle\MarketingListBundle\Form\Type;

use Oro\Bundle\EntityBundle\Form\Type\EntityChoiceType;

class ContactInformationEntityChoiceType extends EntityChoiceType
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'orocrm_marketing_list_contact_information_entity_choice';
    }
}
