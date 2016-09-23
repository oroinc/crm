<?php

namespace Oro\Bundle\MarketingListBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class ContactInformationColumnConstraint extends Constraint
{
    /**
     * @var string
     */
    public $message = 'oro.marketinglist.contact_information_required';

    /**
     * @var string
     */
    public $typeMessage = 'oro.marketinglist.contact_information_type';

    /**
     * @var string
     */
    public $field;

    /**
     * @var string
     */
    public $type;

    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return [self::CLASS_CONSTRAINT, self::PROPERTY_CONSTRAINT];
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
        return 'oro_marketing_list.contact_information_column_validator';
    }
}
