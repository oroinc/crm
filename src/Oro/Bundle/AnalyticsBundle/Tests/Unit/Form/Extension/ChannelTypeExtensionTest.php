<?php

namespace Oro\Bundle\AnalyticsBundle\Tests\Unit\Form\Extension;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\PersistentCollection;
use Oro\Bundle\AnalyticsBundle\Entity\RFMMetricCategory;
use Oro\Bundle\AnalyticsBundle\Form\Extension\ChannelTypeExtension;
use Oro\Bundle\AnalyticsBundle\Form\Type\RFMCategorySettingsType;
use Oro\Bundle\AnalyticsBundle\Validator\CategoriesConstraint;
use Oro\Bundle\ChannelBundle\Form\Type\ChannelType;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Component\TestUtils\ORM\Mocks\UnitOfWork;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;

class ChannelTypeExtensionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ChannelTypeExtension
     */
    protected $extension;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|DoctrineHelper
     */
    protected $doctrineHelper;

    protected function setUp(): void
    {
        $this->doctrineHelper = $this->getMockBuilder('Oro\Bundle\EntityBundle\ORM\DoctrineHelper')
            ->disableOriginalConstructor()
            ->getMock();

        $this->extension = new ChannelTypeExtension(
            $this->doctrineHelper,
            'Oro\Bundle\AnalyticsBundle\Model\RFMAwareInterface',
            'Oro\Bundle\AnalyticsBundle\Entity\RFMMetricCategory'
        );
    }

    public function testBuildForm()
    {
        /** @var \PHPUnit\Framework\MockObject\MockObject|FormBuilderInterface $builder */
        $builder = $this->createMock('Symfony\Component\Form\FormBuilderInterface');

        $builder->expects($this->atLeastOnce())->method('addEventListener');

        $this->extension->buildForm($builder, []);
    }

    /**
     * @param \PHPUnit\Framework\MockObject\MockObject $channel
     * @param int $expectedPersist
     * @param int $expectedRemove
     *
     * @dataProvider postSubmitDataProvider
     */
    public function testPostSubmit($channel, $expectedPersist = null, $expectedRemove = null)
    {
        /** @var \PHPUnit\Framework\MockObject\MockObject|FormEvent $event */
        $event = $this->getMockBuilder('Symfony\Component\Form\FormEvent')->disableOriginalConstructor()->getMock();

        $event->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($channel));

        $form = $this->createMock('Symfony\Component\Form\FormInterface');
        $event->expects($this->any())
            ->method('getForm')
            ->will($this->returnValue($form));

        $childForm = $this->createMock('Symfony\Component\Form\FormInterface');
        $form->expects($this->any())
            ->method('get')
            ->will($this->returnValue($childForm));

        $form->expects($this->any())
            ->method('has')
            ->will($this->returnValue(true));

        /** @var \PHPUnit\Framework\MockObject\MockObject|EntityManager $em */
        $em = $this->getMockBuilder('Doctrine\ORM\EntityManager')->disableOriginalConstructor()->getMock();

        $removeEntity = new RFMMetricCategory();
        $collection = $this->getCollection([$removeEntity, new RFMMetricCategory()]);
        $insertEntity = new RFMMetricCategory();
        $collection->add($insertEntity);
        $collection->remove(0);

        $childForm->expects($this->any())
            ->method('getData')
            ->will($this->onConsecutiveCalls(true, $collection, $this->getCollection(), $this->getCollection()));

        $this->doctrineHelper->expects($this->any())
            ->method('getEntityManager')
            ->will($this->returnValue($em));

        if ($expectedPersist) {
            $em->expects($this->once())->method('persist')->with($this->equalTo($insertEntity));
        }

        if ($expectedRemove) {
            $em->expects($this->once())->method('remove')->with($this->equalTo($removeEntity));
        }

        $this->extension->manageCategories($event);
    }

    /**
     * @return array
     */
    public function postSubmitDataProvider()
    {
        return [
            'empty channel' => [
                null
            ],
            'empty customer identity' => [
                $this->getChannelMock()
            ],
            'identity class without stats' => [
                $this->getChannelMock('\stdClass')
            ],
            'supported identity' => [
                $this->getChannelMock('Oro\Bundle\AnalyticsBundle\Tests\Unit\Model\Stub\RFMAwareStub'),
                1,
                1
            ],
        ];
    }

    /**
     * @param string $identityClass
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    protected function getChannelMock($identityClass = null)
    {
        $channel = $this->createMock('Oro\Bundle\ChannelBundle\Entity\Channel');

        if ($identityClass) {
            $channel->expects($this->any())
                ->method('getCustomerIdentity')
                ->will($this->returnValue($identityClass));
        }

        return $channel;
    }

    /**
     * @dataProvider preSetDataProvider
     */
    public function testPreSetData(array $categories)
    {
        $channel = $this->getChannelMock('Oro\Bundle\AnalyticsBundle\Tests\Unit\Model\Stub\RFMAwareStub');

        /** @var \PHPUnit\Framework\MockObject\MockObject|FormEvent $event */
        $event = $this->getMockBuilder('Symfony\Component\Form\FormEvent')->disableOriginalConstructor()->getMock();

        $event->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($channel));

        $repository = $this->getMockBuilder('Doctrine\ORM\EntityRepository')->disableOriginalConstructor()->getMock();

        $this->doctrineHelper->expects($this->once())
            ->method('getEntityRepository')
            ->will($this->returnValue($repository));

        $repository->expects($this->once())
            ->method('findBy')
            ->with($this->isType('array'))
            ->will($this->returnValue($categories));

        $form = $this->createMock('Symfony\Component\Form\FormInterface');
        $event->expects($this->any())
            ->method('getForm')
            ->will($this->returnValue($form));

        $em = $this->getMockBuilder('Doctrine\ORM\EntityManager')->disableOriginalConstructor()->getMock();
        $metadata = $this->getMockBuilder('Doctrine\ORM\Mapping\ClassMetadata')
            ->disableOriginalConstructor()
            ->getMock();

        $this->doctrineHelper->expects($this->exactly(sizeof(RFMMetricCategory::$types)))
            ->method('getEntityManager')
            ->will($this->returnValue($em));

        $this->doctrineHelper->expects($this->exactly(sizeof(RFMMetricCategory::$types)))
            ->method('getEntityMetadata')
            ->will($this->returnValue($metadata));

        if ($categories) {
            $form->expects($this->exactly(4))
                ->method('add')
                ->withConsecutive(
                    [
                        $this->equalTo('rfm_enabled'),
                        $this->isType('string'),
                        $this->isType('array')
                    ],
                    [
                        $this->equalTo('recency'),
                        $this->equalTo(RFMCategorySettingsType::class),
                        $this->callback(
                            function ($options) {
                                $this->assertEquals(
                                    $this->getCollection([$this->getCategory(RFMMetricCategory::TYPE_RECENCY)]),
                                    $options['data']
                                );

                                return true;
                            }
                        ),
                    ],
                    [
                        $this->equalTo('frequency'),
                        $this->equalTo(RFMCategorySettingsType::class),
                        $this->callback(
                            function ($options) {
                                $this->assertEquals(
                                    $this->getCollection([1 => $this->getCategory(RFMMetricCategory::TYPE_FREQUENCY)]),
                                    $options['data']
                                );

                                return true;
                            }
                        ),
                    ],
                    [
                        $this->equalTo('monetary'),
                        $this->equalTo(RFMCategorySettingsType::class),
                        $this->callback(
                            function ($options) {
                                $this->assertEquals($this->getCollection([]), $options['data']);

                                return true;
                            }
                        ),
                    ]
                );
        }

        $this->extension->loadCategories($event);
    }

    /**
     * @return array
     */
    public function preSetDataProvider()
    {
        return [
            'empty' => [[]],
            'filter' => [
                [
                    $this->getCategory(RFMMetricCategory::TYPE_RECENCY),
                    $this->getCategory(RFMMetricCategory::TYPE_FREQUENCY)
                ]
            ],
        ];
    }

    /**
     * @param \PHPUnit\Framework\MockObject\MockObject $channel
     * @param bool $hasStateForm
     * @param bool $isEnabled
     * @param array $actualData
     * @param array $expectedData
     *
     * @dataProvider stateDataProvider
     */
    public function testHandleState(
        $channel,
        $hasStateForm,
        $isEnabled = null,
        $actualData = null,
        $expectedData = null
    ) {
        /** @var \PHPUnit\Framework\MockObject\MockObject|FormEvent $event */
        $event = $this->getMockBuilder('Symfony\Component\Form\FormEvent')->disableOriginalConstructor()->getMock();

        $event->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($channel));

        $form = $this->createMock('Symfony\Component\Form\FormInterface');
        $form->expects($this->any())
            ->method('has')
            ->will($this->returnValue($hasStateForm));
        $event->expects($this->any())
            ->method('getForm')
            ->will($this->returnValue($form));

        $stateForm = $this->createMock('Symfony\Component\Form\FormInterface');
        $form->expects($this->any())
            ->method('get')
            ->will($this->returnValue($stateForm));
        $stateForm->expects($this->any())
            ->method('getData')
            ->will($this->returnValue($isEnabled));

        if ($actualData) {
            $channel->expects($this->any())
                ->method('getData')
                ->will($this->returnValue($actualData));
        }

        if ($expectedData) {
            $channel->expects($this->any())
                ->method('setData')
                ->will($this->returnValue($expectedData));
        } else {
            $channel->expects($this->never())->method('setData');
        }

        $this->extension->handleState($event);
    }

    /**
     * @return array
     */
    public function stateDataProvider()
    {
        return [
            'empty customer identity' => [$this->getChannelMock(), false],
            'has not state form' => [
                $this->getChannelMock('Oro\Bundle\AnalyticsBundle\Tests\Unit\Model\Stub\RFMAwareStub'),
                false,
            ],
            'empty data' => [
                'channel' => $this->getChannelMock('Oro\Bundle\AnalyticsBundle\Tests\Unit\Model\Stub\RFMAwareStub'),
                'hasStateForm' => true,
                'isEnabled' => false,
                'actualData' => [],
                'expectedData' => ['rfm_enabled' => false],
            ],
            'data was not changed' => [
                'channel' => $this->getChannelMock('Oro\Bundle\AnalyticsBundle\Tests\Unit\Model\Stub\RFMAwareStub'),
                'hasStateForm' => true,
                'isEnabled' => false,
                'actualData' => ['rfm_enabled' => false],
            ],
            'enable' => [
                'channel' => $this->getChannelMock('Oro\Bundle\AnalyticsBundle\Tests\Unit\Model\Stub\RFMAwareStub'),
                'hasStateForm' => true,
                'isEnabled' => true,
                'actualData' => ['rfm_enabled' => false],
                'expectedData' => ['rfm_enabled' => true],
            ],
            'disable' => [
                'channel' => $this->getChannelMock('Oro\Bundle\AnalyticsBundle\Tests\Unit\Model\Stub\RFMAwareStub'),
                'hasStateForm' => true,
                'isEnabled' => false,
                'actualData' => ['rfm_enabled' => true],
                'expectedData' => ['rfm_enabled' => true, 'rfm_require_drop' => true],
            ],
        ];
    }

    /**
     * @param string $type
     *
     * @return CategoriesConstraint
     */
    protected function getConstraint($type)
    {
        $constraint = new CategoriesConstraint();
        $constraint->setType($type);

        return $constraint;
    }

    /**
     * @param array $items
     *
     * @return PersistentCollection
     */
    protected function getCollection(array $items = [])
    {
        $uow = new UnitOfWork();
        /** @var \PHPUnit\Framework\MockObject\MockObject|EntityManager $em */
        $em = $this->getMockBuilder('Doctrine\ORM\EntityManager')->disableOriginalConstructor()->getMock();
        $em->expects($this->any())
            ->method('getUnitOfWork')
            ->willReturn($uow);

        /** @var \PHPUnit\Framework\MockObject\MockObject|ClassMetadata $metadata */
        $metadata = $this->getMockBuilder('Doctrine\ORM\Mapping\ClassMetadata')
            ->disableOriginalConstructor()
            ->getMock();

        $collection = new PersistentCollection($em, $metadata, new ArrayCollection($items));

        $collection->takeSnapshot();

        return $collection;
    }

    /**
     * @param string $type
     *
     * @return RFMMetricCategory
     */
    protected function getCategory($type)
    {
        $category = new RFMMetricCategory();
        $category->setCategoryType($type);

        return $category;
    }

    public function testGetExtendedTypes()
    {
        $this->assertEquals([ChannelType::class], ChannelTypeExtension::getExtendedTypes());
    }

    /**
     * @param bool $feature
     * @param array $expected
     *
     * @dataProvider validationGroupsDataProvider
     */
    public function testSetDefaults($feature, array $expected)
    {
        $form = $this->createMock('Symfony\Component\Form\FormInterface');
        $form->expects($this->any())
            ->method('get')
            ->will($this->returnValue($form));
        $form->expects($this->any())
            ->method('has')
            ->will($this->returnValue($feature));
        $form->expects($this->any())
            ->method('getData')
            ->will($this->returnValue($feature));

        $reflector = new \ReflectionClass(get_class($this->extension));
        $method = $reflector->getMethod('getValidationGroups');
        $method->setAccessible(true);
        /** @var callable $result */
        $result = $method->invokeArgs($this->extension, []);

        $this->assertEquals($expected, $result($form));
    }

    /**
     * @return array
     */
    public function validationGroupsDataProvider()
    {
        return [
            'validate' => [
                true,
                ['Default', 'RFMCategories']
            ],
            'not validate' => [
                false,
                ['Default']
            ],
        ];
    }
}
