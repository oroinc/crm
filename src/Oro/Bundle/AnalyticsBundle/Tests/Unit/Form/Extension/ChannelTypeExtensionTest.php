<?php

namespace Oro\Bundle\AnalyticsBundle\Tests\Unit\Form\Extension;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\PersistentCollection;
use Doctrine\ORM\UnitOfWork;
use Oro\Bundle\AnalyticsBundle\Entity\RFMMetricCategory;
use Oro\Bundle\AnalyticsBundle\Form\Extension\ChannelTypeExtension;
use Oro\Bundle\AnalyticsBundle\Form\Type\RFMCategorySettingsType;
use Oro\Bundle\AnalyticsBundle\Model\RFMAwareInterface;
use Oro\Bundle\AnalyticsBundle\Tests\Unit\Model\Stub\RFMAwareStub;
use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\ChannelBundle\Form\Type\ChannelType;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Component\Testing\ReflectionUtil;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormInterface;

class ChannelTypeExtensionTest extends \PHPUnit\Framework\TestCase
{
    /** @var DoctrineHelper|\PHPUnit\Framework\MockObject\MockObject */
    private $doctrineHelper;

    /** @var ChannelTypeExtension */
    private $extension;

    protected function setUp(): void
    {
        $this->doctrineHelper = $this->createMock(DoctrineHelper::class);

        $this->extension = new ChannelTypeExtension(
            $this->doctrineHelper,
            RFMAwareInterface::class,
            RFMMetricCategory::class
        );
    }

    public function testBuildForm()
    {
        $builder = $this->createMock(FormBuilderInterface::class);

        $builder->expects($this->atLeastOnce())
            ->method('addEventListener');

        $this->extension->buildForm($builder, []);
    }

    /**
     * @dataProvider postSubmitDataProvider
     */
    public function testPostSubmit(?Channel $channel, int $expectedPersist = null, int $expectedRemove = null)
    {
        $event = $this->createMock(FormEvent::class);

        $event->expects($this->once())
            ->method('getData')
            ->willReturn($channel);

        $form = $this->createMock(FormInterface::class);
        $event->expects($this->any())
            ->method('getForm')
            ->willReturn($form);

        $childForm = $this->createMock(FormInterface::class);
        $form->expects($this->any())
            ->method('get')
            ->willReturn($childForm);

        $form->expects($this->any())
            ->method('has')
            ->willReturn(true);

        $em = $this->createMock(EntityManager::class);

        $removeEntity = new RFMMetricCategory();
        $collection = $this->getCollection([$removeEntity, new RFMMetricCategory()]);
        $insertEntity = new RFMMetricCategory();
        $collection->add($insertEntity);
        $collection->remove(0);

        $childForm->expects($this->any())
            ->method('getData')
            ->willReturnOnConsecutiveCalls(true, $collection, $this->getCollection(), $this->getCollection());

        $this->doctrineHelper->expects($this->any())
            ->method('getEntityManager')
            ->willReturn($em);

        if ($expectedPersist) {
            $em->expects($this->once())
                ->method('persist')
                ->with($this->identicalTo($insertEntity));
        }

        if ($expectedRemove) {
            $em->expects($this->once())
                ->method('remove')
                ->with($this->identicalTo($removeEntity));
        }

        $this->extension->manageCategories($event);
    }

    public function postSubmitDataProvider(): array
    {
        return [
            'empty channel' => [
                null
            ],
            'empty customer identity' => [
                $this->getChannel()
            ],
            'identity class without stats' => [
                $this->getChannel(\stdClass::class)
            ],
            'supported identity' => [
                $this->getChannel(RFMAwareStub::class),
                1,
                1
            ],
        ];
    }

    private function getChannel(string $identityClass = null): Channel
    {
        $channel = $this->createMock(Channel::class);
        if ($identityClass) {
            $channel->expects($this->any())
                ->method('getCustomerIdentity')
                ->willReturn($identityClass);
        }

        return $channel;
    }

    /**
     * @dataProvider preSetDataProvider
     */
    public function testPreSetData(array $categories)
    {
        $channel = $this->getChannel(RFMAwareStub::class);

        $event = $this->createMock(FormEvent::class);

        $event->expects($this->once())
            ->method('getData')
            ->willReturn($channel);

        $repository = $this->createMock(EntityRepository::class);

        $this->doctrineHelper->expects($this->once())
            ->method('getEntityRepository')
            ->willReturn($repository);

        $repository->expects($this->once())
            ->method('findBy')
            ->with($this->isType('array'))
            ->willReturn($categories);

        $form = $this->createMock(FormInterface::class);
        $event->expects($this->any())
            ->method('getForm')
            ->willReturn($form);

        $em = $this->createMock(EntityManager::class);
        $metadata = $this->createMock(ClassMetadata::class);

        $this->doctrineHelper->expects($this->exactly(count(RFMMetricCategory::$types)))
            ->method('getEntityManager')
            ->willReturn($em);

        $this->doctrineHelper->expects($this->exactly(count(RFMMetricCategory::$types)))
            ->method('getEntityMetadata')
            ->willReturn($metadata);

        if ($categories) {
            $form->expects($this->exactly(4))
                ->method('add')
                ->withConsecutive(
                    [
                        'rfm_enabled',
                        $this->isType('string'),
                        $this->isType('array')
                    ],
                    [
                        'recency',
                        RFMCategorySettingsType::class,
                        $this->callback(function ($options) {
                            $this->assertEquals(
                                $this->getCollection([$this->getCategory(RFMMetricCategory::TYPE_RECENCY)]),
                                $options['data']
                            );

                            return true;
                        }),
                    ],
                    [
                        'frequency',
                        RFMCategorySettingsType::class,
                        $this->callback(function ($options) {
                            $this->assertEquals(
                                $this->getCollection([1 => $this->getCategory(RFMMetricCategory::TYPE_FREQUENCY)]),
                                $options['data']
                            );

                            return true;
                        }),
                    ],
                    [
                        'monetary',
                        RFMCategorySettingsType::class,
                        $this->callback(function ($options) {
                            $this->assertEquals($this->getCollection([]), $options['data']);

                            return true;
                        }),
                    ]
                );
        }

        $this->extension->loadCategories($event);
    }

    public function preSetDataProvider(): array
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
     * @dataProvider stateDataProvider
     */
    public function testHandleState(
        Channel|\PHPUnit\Framework\MockObject\MockObject $channel,
        bool $hasStateForm,
        bool $isEnabled = null,
        array $actualData = null,
        array $expectedData = null
    ) {
        $event = $this->createMock(FormEvent::class);
        $event->expects($this->once())
            ->method('getData')
            ->willReturn($channel);

        $form = $this->createMock(FormInterface::class);
        $form->expects($this->any())
            ->method('has')
            ->willReturn($hasStateForm);
        $event->expects($this->any())
            ->method('getForm')
            ->willReturn($form);

        $stateForm = $this->createMock(FormInterface::class);
        $form->expects($this->any())
            ->method('get')
            ->willReturn($stateForm);
        $stateForm->expects($this->any())
            ->method('getData')
            ->willReturn($isEnabled);

        if ($actualData) {
            $channel->expects($this->any())
                ->method('getData')
                ->willReturn($actualData);
        }

        if ($expectedData) {
            $channel->expects($this->any())
                ->method('setData')
                ->willReturn($expectedData);
        } else {
            $channel->expects($this->never())
                ->method('setData');
        }

        $this->extension->handleState($event);
    }

    public function stateDataProvider(): array
    {
        return [
            'empty customer identity' => [$this->getChannel(), false],
            'has not state form' => [
                $this->getChannel(RFMAwareStub::class),
                false,
            ],
            'empty data' => [
                'channel' => $this->getChannel(RFMAwareStub::class),
                'hasStateForm' => true,
                'isEnabled' => false,
                'actualData' => [],
                'expectedData' => ['rfm_enabled' => false],
            ],
            'data was not changed' => [
                'channel' => $this->getChannel(RFMAwareStub::class),
                'hasStateForm' => true,
                'isEnabled' => false,
                'actualData' => ['rfm_enabled' => false],
            ],
            'enable' => [
                'channel' => $this->getChannel(RFMAwareStub::class),
                'hasStateForm' => true,
                'isEnabled' => true,
                'actualData' => ['rfm_enabled' => false],
                'expectedData' => ['rfm_enabled' => true],
            ],
            'disable' => [
                'channel' => $this->getChannel(RFMAwareStub::class),
                'hasStateForm' => true,
                'isEnabled' => false,
                'actualData' => ['rfm_enabled' => true],
                'expectedData' => ['rfm_enabled' => true, 'rfm_require_drop' => true],
            ],
        ];
    }

    private function getCollection(array $items = []): PersistentCollection
    {
        $em = $this->createMock(EntityManager::class);
        $em->expects($this->any())
            ->method('getUnitOfWork')
            ->willReturn($this->createMock(UnitOfWork::class));

        $metadata = $this->createMock(ClassMetadata::class);

        $collection = new PersistentCollection($em, $metadata, new ArrayCollection($items));
        $collection->takeSnapshot();

        return $collection;
    }

    private function getCategory(string $type): RFMMetricCategory
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
     * @dataProvider validationGroupsDataProvider
     */
    public function testSetDefaults(bool $feature, array $expected)
    {
        $form = $this->createMock(FormInterface::class);
        $form->expects($this->any())
            ->method('get')
            ->willReturn($form);
        $form->expects($this->any())
            ->method('has')
            ->willReturn($feature);
        $form->expects($this->any())
            ->method('getData')
            ->willReturn($feature);

        /** @var callable $result */
        $result = ReflectionUtil::callMethod($this->extension, 'getValidationGroups', []);

        $this->assertEquals($expected, $result($form));
    }

    public function validationGroupsDataProvider(): array
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
