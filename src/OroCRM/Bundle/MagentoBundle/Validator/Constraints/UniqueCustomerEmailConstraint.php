<?php

namespace OroCRM\Bundle\MagentoBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class UniqueCustomerEmailConstraint extends Constraint
{
    /**
     * @var string
     */
    public $message = 'orocrm.magento.unique_customer_email.message';

    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return [self::CLASS_CONSTRAINT];
    }

    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return 'orocrm_magento.validator.unique_customer_email';
    }
}
