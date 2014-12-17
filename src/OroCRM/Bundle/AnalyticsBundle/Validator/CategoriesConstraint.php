<?php

namespace OroCRM\Bundle\AnalyticsBundle\Validator;

use Symfony\Component\Validator\Constraint;

class CategoriesConstraint extends Constraint
{
    const GROUP = 'RFMCategories';

    /**
     * @var string
     */
    public $message = 'orocrm.analytics.validator.categories.order';

    /**
     * @var string
     */
    public $countMessage = 'orocrm.analytics.validator.categories.count';

    /**
     * @var string
     */
    public $blankMessage = 'orocrm.analytics.validator.categories.blank';

    /**
     * @var string
     */
    protected $type;

    /**
     * {@inheritdoc}
     */
    public $groups = [self::GROUP];

    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return 'orocrm_analytics.categories_validator';
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }
}
