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

        if ($this->validateCount($value, $constraint) && $this->validateBlank($value, $constraint)) {
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
        $orderedByIndex = $value->matching(new Criteria(null, ['categoryIndex' => Criteria::ASC]));
        $isIncreasing = $this->isIncreasing($orderedByIndex);

        if ($isIncreasing) {
            $firstMax = $orderedByIndex->first()->getMaxValue();
            $lastMin = $orderedByIndex->last()->getMinValue();
            $hasEmpty = $this->isEmpty($firstMax) || $this->isEmpty($lastMin);
        } else {
            $firstMin = $orderedByIndex->first()->getMinValue();
            $lastMax = $orderedByIndex->last()->getMaxValue();
            $hasEmpty = $this->isEmpty($firstMin) || $this->isEmpty($lastMax);
        }

        if (!$hasEmpty) {
            $orderedByIndexWithoutEmpty = $orderedByIndex->filter(
                function (RFMMetricCategory $category) use ($orderedByIndex) {
                    return !in_array($category, [$orderedByIndex->first(), $orderedByIndex->last()], true);
                }
            );

            /** @var RFMMetricCategory $category */
            foreach ($orderedByIndexWithoutEmpty->toArray() as $category) {
                $min = $category->getMinValue();
                $max = $category->getMaxValue();

                if ($this->isEmpty($min) || $this->isEmpty($max)) {
                    $hasEmpty = true;
                    break;
                }
            }
        }

        if ($hasEmpty) {
            $this->context->addViolationAt($constraint->getType(), $constraint->blankMessage);
        }

        return !$hasEmpty;
    }

    /**
     * @param mixed $value
     * @return bool
     */
    protected function isEmpty($value)
    {
        return $value === '' || $value === null;
    }

    /**
     * Check that number of categories not less than minimum defined number.
     *
     * @param PersistentCollection $value
     * @param CategoriesConstraint $constraint
     * @return bool
     */
    protected function validateCount(PersistentCollection $value, CategoriesConstraint $constraint)
    {
        if ($value->count() >= self::MIN_CATEGORIES_COUNT) {
            return true;
        }

        $this->context->addViolationAt(
            $constraint->getType(),
            $constraint->countMessage,
            ['%count%' => self::MIN_CATEGORIES_COUNT]
        );

        return false;
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
        if ($value->isEmpty()) {
            return;
        }

        $orderedByIndex = $value->matching(new Criteria(null, ['categoryIndex' => Criteria::ASC]));
        $isIncreasing = $this->isIncreasing($orderedByIndex);

        if ($isIncreasing) {
            $orderIsValid = $this->validateOrderAsk($orderedByIndex->toArray());
            $criteria = Criteria::ASC;
        } else {
            $orderIsValid = $this->validateOrderDesc($orderedByIndex->toArray());
            $criteria = Criteria::DESC;
        }

        if (!$orderIsValid) {
            $this->context->addViolationAt($constraint->getType(), $constraint->message, ['%order%' => $criteria]);
        }
    }

    /**
     * @param array $orderedData
     * @return bool
     */
    protected function validateOrderAsk(array $orderedData)
    {
        $lastValue = 0;

        /** @var RFMMetricCategory $item */
        foreach ($orderedData as $item) {
            $maxValue = $item->getMaxValue();

            if ($lastValue >= $maxValue || $item->getMinValue() >= $maxValue) {
                return false;
            }

            $lastValue = $maxValue;
        }

        return true;
    }

    /**
     * @param array $orderedData
     * @return bool
     */
    protected function validateOrderDesc(array $orderedData)
    {
        $lastValue = PHP_INT_MAX;

        /** @var RFMMetricCategory $item */
        foreach ($orderedData as $item) {
            $minValue = $item->getMinValue();

            if ($lastValue <= $minValue) {
                return false;
            }

            $lastValue = $minValue;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return 'orocrm_analytics.validator.categories';
    }

    /**
     * @param Collection $orderedByIndex
     * @return bool
     */
    protected function isIncreasing(Collection $orderedByIndex)
    {
        return is_null($orderedByIndex->first()->getMinValue())
        && is_null($orderedByIndex->last()->getMaxValue());
    }
}
