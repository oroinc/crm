<?php

namespace Oro\Bundle\AnalyticsBundle\Validator;

use Symfony\Component\Validator\Constraint;

class CategoriesConstraint extends Constraint
{
    const GROUP = 'RFMCategories';

    /**
     * @var string
     */
    public $message = 'oro.analytics.validator.categories.order';

    /**
     * @var string
     */
    public $countMessage = 'oro.analytics.validator.categories.count';

    /**
     * @var string
     */
    public $blankMessage = 'oro.analytics.validator.categories.blank';

    /**
     * @var string
     */
    protected $type;

    public $groups = [self::GROUP];

    #[\Override]
    public function validatedBy(): string
    {
        return 'oro_analytics.categories_validator';
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
