<?php

namespace Oro\Bundle\AnalyticsBundle\Tests\Unit\Validator;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\PersistentCollection;
use Oro\Bundle\AnalyticsBundle\Entity\RFMMetricCategory;
use Oro\Bundle\AnalyticsBundle\Validator\CategoriesConstraint;
use Oro\Bundle\AnalyticsBundle\Validator\CategoriesValidator;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

class CategoriesValidatorTest extends ConstraintValidatorTestCase
{
    protected function createValidator()
    {
        return new CategoriesValidator();
    }

    /**
     * @dataProvider validateDataProvider
     */
    public function testValidate(
        ?PersistentCollection $collection,
        string $type,
        string $expectedViolationsMessage = null,
        array $parameters = []
    ) {
        $constraint = new CategoriesConstraint(['type' => $type]);
        $this->validator->validate($collection, $constraint);

        if (null === $expectedViolationsMessage) {
            $this->assertNoViolation();
        } else {
            $this->buildViolation($expectedViolationsMessage)
                ->setParameters($parameters)
                ->atPath('property.path.' . $type)
                ->assertRaised();
        }
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function validateDataProvider(): array
    {
        $constraint = new CategoriesConstraint();

        return [
            'not collection' => [null, RFMMetricCategory::TYPE_FREQUENCY],
            'count violation' => [
                'collection' => $this->getCollection(
                    [
                        $this->getCategory(RFMMetricCategory::TYPE_FREQUENCY, 1, null, 100),
                        $this->getCategory(RFMMetricCategory::TYPE_FREQUENCY, 2, 100, null),
                        $this->getCategory(RFMMetricCategory::TYPE_FREQUENCY, 3, 1000, null),
                    ]
                ),
                'type' => RFMMetricCategory::TYPE_FREQUENCY,
                'expectedViolationsMessage' => $constraint->blankMessage
            ],
            'ordered' => [
                'collection' => $this->getCollection(
                    [
                        $this->getCategory(RFMMetricCategory::TYPE_FREQUENCY, 1, null, 20),
                        $this->getCategory(RFMMetricCategory::TYPE_FREQUENCY, 2, 20, 30),
                        $this->getCategory(RFMMetricCategory::TYPE_FREQUENCY, 3, 30, null),
                    ]
                ),
                'type' => RFMMetricCategory::TYPE_FREQUENCY,
            ],
            'asc order violation' => [
                'collection' => $this->getCollection(
                    [
                        $this->getCategory(RFMMetricCategory::TYPE_FREQUENCY, 1, null, 20),
                        $this->getCategory(RFMMetricCategory::TYPE_FREQUENCY, 2, 20, 30),
                        $this->getCategory(RFMMetricCategory::TYPE_FREQUENCY, 3, 10, 40),
                        $this->getCategory(RFMMetricCategory::TYPE_FREQUENCY, 4, 40, null),
                    ]
                ),
                'type' => RFMMetricCategory::TYPE_FREQUENCY,
                'expectedViolationsMessage' => $constraint->message,
                'parameters' => ['%order%' => 'ASC']
            ],
            'desc order' => [
                'collection' => $this->getCollection(
                    [
                        $this->getCategory(RFMMetricCategory::TYPE_FREQUENCY, 1, 30, null),
                        $this->getCategory(RFMMetricCategory::TYPE_FREQUENCY, 2, 20, 30),
                        $this->getCategory(RFMMetricCategory::TYPE_FREQUENCY, 3, null, 20),
                    ]
                ),
                'type' => RFMMetricCategory::TYPE_FREQUENCY,
            ],
            'desc order violation' => [
                'collection' => $this->getCollection(
                    [
                        $this->getCategory(RFMMetricCategory::TYPE_FREQUENCY, 1, 30, null),
                        $this->getCategory(RFMMetricCategory::TYPE_FREQUENCY, 2, 20, 30),
                        $this->getCategory(RFMMetricCategory::TYPE_FREQUENCY, 3, 25, 20),
                        $this->getCategory(RFMMetricCategory::TYPE_FREQUENCY, 4, null, 10),
                    ]
                ),
                'type' => RFMMetricCategory::TYPE_FREQUENCY,
                'expectedViolationsMessage' => $constraint->message,
                'parameters' => ['%order%' => 'DESC']
            ],
            'desc order same value violation' => [
                'collection' => $this->getCollection(
                    [
                        $this->getCategory(RFMMetricCategory::TYPE_FREQUENCY, 1, 30, null),
                        $this->getCategory(RFMMetricCategory::TYPE_FREQUENCY, 2, 20, 30),
                        $this->getCategory(RFMMetricCategory::TYPE_FREQUENCY, 3, 20, 20),
                        $this->getCategory(RFMMetricCategory::TYPE_FREQUENCY, 4, null, 10),
                    ]
                ),
                'type' => RFMMetricCategory::TYPE_FREQUENCY,
                'expectedViolationsMessage' => $constraint->message,
                'parameters' => ['%order%' => 'DESC']
            ],
            'asc order same value violation' => [
                'collection' => $this->getCollection(
                    [
                        $this->getCategory(RFMMetricCategory::TYPE_FREQUENCY, 1, null, 20),
                        $this->getCategory(RFMMetricCategory::TYPE_FREQUENCY, 2, 20, 30),
                        $this->getCategory(RFMMetricCategory::TYPE_FREQUENCY, 3, 30, 30),
                        $this->getCategory(RFMMetricCategory::TYPE_FREQUENCY, 4, 40, null),
                    ]
                ),
                'type' => RFMMetricCategory::TYPE_FREQUENCY,
                'expectedViolationsMessage' => $constraint->message,
                'parameters' => ['%order%' => 'ASC']
            ],
            'blank value violation asc mid' => [
                'collection' => $this->getCollection(
                    [
                        $this->getCategory(RFMMetricCategory::TYPE_FREQUENCY, 1, null, 100),
                        $this->getCategory(RFMMetricCategory::TYPE_FREQUENCY, 2, 100, null),
                        $this->getCategory(RFMMetricCategory::TYPE_FREQUENCY, 3, 1000, null),
                    ]
                ),
                'type' => RFMMetricCategory::TYPE_FREQUENCY,
                'expectedViolationsMessage' => $constraint->blankMessage
            ],
            'blank value violation desc mid' => [
                'collection' => $this->getCollection(
                    [
                        $this->getCategory(RFMMetricCategory::TYPE_FREQUENCY, 1, 1000, null),
                        $this->getCategory(RFMMetricCategory::TYPE_FREQUENCY, 2, null, 1000),
                        $this->getCategory(RFMMetricCategory::TYPE_FREQUENCY, 3, null, 100),
                    ]
                ),
                'type' => RFMMetricCategory::TYPE_FREQUENCY,
                'expectedViolationsMessage' => $constraint->blankMessage
            ],
            'blank value violation asc first empty' => [
                'collection' => $this->getCollection(
                    [
                        $this->getCategory(RFMMetricCategory::TYPE_FREQUENCY, 1, null, null),
                        $this->getCategory(RFMMetricCategory::TYPE_FREQUENCY, 2, 100, 1000),
                        $this->getCategory(RFMMetricCategory::TYPE_FREQUENCY, 3, 1000, null),
                    ]
                ),
                'type' => RFMMetricCategory::TYPE_FREQUENCY,
                'expectedViolationsMessage' => $constraint->blankMessage
            ],
            'blank value violation asc last empty' => [
                'collection' => $this->getCollection(
                    [
                        $this->getCategory(RFMMetricCategory::TYPE_FREQUENCY, 1, null, 100),
                        $this->getCategory(RFMMetricCategory::TYPE_FREQUENCY, 2, 100, 1000),
                        $this->getCategory(RFMMetricCategory::TYPE_FREQUENCY, 3, null, null),
                    ]
                ),
                'type' => RFMMetricCategory::TYPE_FREQUENCY,
                'expectedViolationsMessage' => $constraint->blankMessage
            ],
            'blank value violation desc first empty' => [
                'collection' => $this->getCollection(
                    [
                        $this->getCategory(RFMMetricCategory::TYPE_FREQUENCY, 1, null, null),
                        $this->getCategory(RFMMetricCategory::TYPE_FREQUENCY, 2, 1000, 1000),
                        $this->getCategory(RFMMetricCategory::TYPE_FREQUENCY, 3, null, 100),
                    ]
                ),
                'type' => RFMMetricCategory::TYPE_FREQUENCY,
                'expectedViolationsMessages' => $constraint->blankMessage
            ],
            'blank value violation desc last empty' => [
                'collection' => $this->getCollection(
                    [
                        $this->getCategory(RFMMetricCategory::TYPE_FREQUENCY, 1, 1000, null),
                        $this->getCategory(RFMMetricCategory::TYPE_FREQUENCY, 2, 1000, 1000),
                        $this->getCategory(RFMMetricCategory::TYPE_FREQUENCY, 3, null, null),
                    ]
                ),
                'type' => RFMMetricCategory::TYPE_FREQUENCY,
                'expectedViolationsMessage' => $constraint->blankMessage
            ]
        ];
    }

    private function getCategory(string $type, int $index, ?int $minValue, ?int $maxValue): RFMMetricCategory
    {
        $category = new RFMMetricCategory();
        $category
            ->setCategoryType($type)
            ->setCategoryIndex($index)
            ->setMinValue($minValue)
            ->setMaxValue($maxValue);

        return $category;
    }

    private function getCollection(array $items = []): PersistentCollection
    {
        $collection = new PersistentCollection(
            $this->createMock(EntityManagerInterface::class),
            $this->createMock(ClassMetadata::class),
            new ArrayCollection($items)
        );
        $collection->takeSnapshot();

        return $collection;
    }
}
