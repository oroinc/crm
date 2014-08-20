<?php

namespace OroCRM\Bundle\MarketingListBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class ContactInformationColumnConstraint extends Constraint
{
    /**
     * @var string
     */
    public $message = 'At least one column must contain contact information';

    /**
     * @var string
     */
    public $field;

    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return array(self::CLASS_CONSTRAINT, self::PROPERTY_CONSTRAINT);
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOption()
    {
        return 'field';
    }

    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return 'orocrm_marketing_list.contact_information_column_validator';
    }
}
