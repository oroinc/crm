<?php

namespace OroCRM\Bundle\ReportBundle\Form\Type;

use Oro\Bundle\EntityBundle\Form\Type\EntityFieldChoiceType;

class ReportEntityFieldChoiceType extends EntityFieldChoiceType
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'orocrm_report_entity_field_choice';
    }
}
