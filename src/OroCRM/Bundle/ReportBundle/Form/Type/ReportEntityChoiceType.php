<?php

namespace OroCRM\Bundle\ReportBundle\Form\Type;

use Oro\Bundle\EntityBundle\Form\Type\EntityChoiceType;

class ReportEntityChoiceType extends EntityChoiceType
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'orocrm_report_entity_choice';
    }
}
