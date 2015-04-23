<?php

namespace OroCRM\Bundle\MagentoBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class StartSyncDateConstraint extends Constraint
{
    /**
     * @var string
     */
    public $message = 'orocrm.magento.start_sync_date.message';

    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return 'orocrm_magento.validator.start_sync_date';
    }
}
