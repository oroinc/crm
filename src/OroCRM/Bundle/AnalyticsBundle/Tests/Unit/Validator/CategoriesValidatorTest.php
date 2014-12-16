<?php

namespace OroCRM\Bundle\AnalyticsBundle\Tests\Unit\Validator;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\PersistentCollection;

use Symfony\Component\Validator\ExecutionContextInterface;

use OroCRM\Bundle\AnalyticsBundle\Entity\RFMMetricCategory;
use OroCRM\Bundle\AnalyticsBundle\Validator\CategoriesConstraint;
use OroCRM\Bundle\AnalyticsBundle\Validator\CategoriesValidator;

class CategoriesValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CategoriesValidator
     */
    protected $validator;

    protected function setUp()
    {
        $this->validator = new CategoriesValidator();
    }

    public function testValidatedBy()
    {
        $this->assertInternalType('string', $this->validator->validatedBy());
    }

    /**
     * @param PersistentCollection $collection
     * @param string $type
     * @param array $expectedViolationsMessages
     *
     * @dataProvider validateDataProvider
     */
    public function testValidate($collection, $type, $expectedViolationsMessages = [])
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|ExecutionContextInterface $context */
        $context = $this->getMockForAbstractClass('Symfony\Component\Validator\ExecutionContextInterface');

        $this->validator->initialize($context);

        /** @var \PHPUnit_Framework_MockObject_MockObject|CategoriesConstraint $constraint */
        $constraint = $this->getMock('OroCRM\Bundle\AnalyticsBundle\Validator\CategoriesConstraint');

        foreach ($expectedViolationsMessages as $key => $expectedViolationsMessage) {
            $context->expects($this->at($key))
                ->method('addViolationAt')
                ->with($this->equalTo($type), $this->equalTo($expectedViolationsMessage), $this->isType('array'));
        }

        $constraint->expects($this->any())
            ->method('getType')
            ->will($this->returnValue($type));

        $this->validator->validate($collection, $constraint);
    }

    /**
     * @return array
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function validateDataProvider()
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
                'expectedViolationsMessages' =>
                    [
                        $constraint->blankMessage,
                    ]
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
                'expectedViolationsMessage' => [
                    $constraint->message
                ],
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
                'expectedViolationsMessage' => [
                    $constraint->message
                ],
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
                'expectedViolationsMessage' => [
                    $constraint->message
                ],
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
                'expectedViolationsMessage' => [
                    $constraint->message
                ],
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
                'expectedViolationsMessages' =>
                    [
                        $constraint->blankMessage,
                    ]
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
                'expectedViolationsMessages' =>
                    [
                        $constraint->blankMessage,
                    ]
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
                'expectedViolationsMessages' =>
                    [
                        $constraint->blankMessage,
                    ]
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
                'expectedViolationsMessages' =>
                    [
                        $constraint->blankMessage,
                    ]
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
                'expectedViolationsMessages' =>
                    [
                        $constraint->blankMessage,
                    ]
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
                'expectedViolationsMessages' =>
                    [
                        $constraint->blankMessage,
                    ]
            ]
        ];
    }

    /**
     * @param string $type
     * @param int $index
     * @param int $minValue
     * @param int $maxValue
     *
     * @return RFMMetricCategory
     */
    protected function getCategory($type, $index, $minValue, $maxValue)
    {
        $category = new RFMMetricCategory();

        $category
            ->setCategoryType($type)
            ->setCategoryIndex($index)
            ->setMinValue($minValue)
            ->setMaxValue($maxValue);

        return $category;
    }

    /**
     * @param array $items
     *
     * @return PersistentCollection
     */
    protected function getCollection(array $items = [])
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|EntityManager $em */
        $em = $this->getMockBuilder('Doctrine\ORM\EntityManager')->disableOriginalConstructor()->getMock();

        /** @var \PHPUnit_Framework_MockObject_MockObject|ClassMetadata $metadata */
        $metadata = $this->getMockBuilder('Doctrine\ORM\Mapping\ClassMetadata')
            ->disableOriginalConstructor()
            ->getMock();

        $collection = new PersistentCollection($em, $metadata, new ArrayCollection($items));

        $collection->takeSnapshot();

        return $collection;
    }
}
