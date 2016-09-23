<?php

namespace Oro\Bundle\MagentoBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class StartSyncDateConstraint extends Constraint
{
    /**
     * @var string
     */
    public $message = 'oro.magento.start_sync_date.message';

    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return 'oro_magento.validator.start_sync_date';
    }
}
