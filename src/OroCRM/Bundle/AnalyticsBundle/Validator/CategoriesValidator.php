<?php

namespace OroCRM\Bundle\AnalyticsBundle\Validator;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\PersistentCollection;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

use OroCRM\Bundle\AnalyticsBundle\Entity\RFMMetricCategory;

class CategoriesValidator extends ConstraintValidator
{
    const MIN_CATEGORIES_COUNT = 2;

    /**
     * Validate collection.
     *
     * @param PersistentCollection|RFMMetricCategory[] $value
     * @param CategoriesConstraint $constraint
     *
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$value instanceof PersistentCollection) {
            return;
        }

        $this->validateCount($value, $constraint);
        if ($this->validateBlank($value, $constraint)) {
            $this->validateOrder($value, $constraint);
        }
    }

    /**
     * Check collection for empty values.
     *
     * @param PersistentCollection $value
     * @param CategoriesConstraint $constraint
     * @return bool
     */
    protected function validateBlank(PersistentCollection $value, CategoriesConstraint $constraint)
    {
        if (!$this->filterNotEmptyFields($value)->isEmpty()) {
            $this->context->addViolationAt($constraint->getType(), $constraint->blankMessage);
            return false;
        }

        return true;
    }

    /**
     * Check that number of categories not less than minimum defined number.
     *
     * @param PersistentCollection $value
     * @param CategoriesConstraint $constraint
     */
    protected function validateCount(PersistentCollection $value, CategoriesConstraint $constraint)
    {
        if ($value->count() >= self::MIN_CATEGORIES_COUNT) {
            return;
        }

        $this->context->addViolationAt(
            $constraint->getType(),
            $constraint->countMessage,
            ['%count%' => self::MIN_CATEGORIES_COUNT]
        );
    }

    /**
     * Check that collection is in right order.
     *
     * For increasing collection values must be in ascending order.
     * For decreasing collection value must be in descending order.
     *
     * @param PersistentCollection $value
     * @param CategoriesConstraint $constraint
     */
    protected function validateOrder(PersistentCollection $value, CategoriesConstraint $constraint)
    {
        if ($value->isEmpty() || count($this->filterNotEmptyFields($value)) > 0) {
            return;
        }

        $orderedByIndex = $value->matching(new Criteria(null, ['categoryIndex' => Criteria::ASC]));

        $isIncreasing = is_null($orderedByIndex->first()->getMinValue())
            && is_null($orderedByIndex->last()->getMaxValue());

        if ($isIncreasing) {
            $criteria = Criteria::ASC;
        } else {
            $criteria = Criteria::DESC;
        }

        $orderedByValue = $value->matching(
            new Criteria(null, ['minValue' => $criteria])
        );

        if ($orderedByValue->toArray() !== $orderedByIndex->toArray()) {
            $this->context->addViolationAt($constraint->getType(), $constraint->message, ['%order%' => $criteria]);

            return;
        }

        if (!$isIncreasing) {
            return;
        }

        $invalidItems = $orderedByValue->filter(
            function (RFMMetricCategory $category) {
                $maxValue = $category->getMaxValue();
                if (!$maxValue) {
                    return false;
                }

                return $category->getMinValue() >= $maxValue;
            }
        );

        if (!$invalidItems->isEmpty()) {
            $this->context->addViolationAt($constraint->getType(), $constraint->message, ['%order%' => $criteria]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return 'orocrm_analytics.validator.categories';
    }

    /**
     * @param Collection $values
     * @return Collection
     */
    protected function filterNotEmptyFields($values)
    {
        return $values->filter(
            function (RFMMetricCategory $category) {
                return $category->getMaxValue() === null && $category->getMinValue() === null;
            }
        );
    }
}
