<?php

namespace Oro\Bundle\ChannelBundle\Tests\Unit\Provider;

use Doctrine\ORM\Mapping\ClassMetadata;
use Oro\Bundle\ChannelBundle\Provider\EntityExclusionProvider;
use Oro\Bundle\ChannelBundle\Provider\SettingsProvider;
use Oro\Bundle\ChannelBundle\Provider\StateProvider;

class EntityExclusionProviderTest extends \PHPUnit\Framework\TestCase
{
    private const TEST_MAIN_ENTITY_NAME               = 'TestBundle\Entity\TestMain';
    private const TEST_ENTITY_NAME                    = 'TestBundle\Entity\Test';
    private const TEST_DEPENDENCY_ENTITY_NAME         = 'TestBundle\Entity\Test1';
    private const TEST_ANOTHER_DEPENDENCY_ENTITY_NAME = 'TestBundle\Entity\Test12';
    private const TEST_ASSOC_NAME                     = 'relation';
    private const TEST_FIELD_NAME                     = 'field';

    /** @var SettingsProvider|\PHPUnit\Framework\MockObject\MockObject */
    private $settingsProvider;

    /** @var StateProvider|\PHPUnit\Framework\MockObject\MockObject */
    private $stateProvider;

    /** @var EntityExclusionProvider */
    private $exclusionProvider;

    protected function setUp()
    {
        $this->settingsProvider = $this->createMock(SettingsProvider::class);
        $this->stateProvider = $this->createMock(StateProvider::class);

        $this->exclusionProvider = new EntityExclusionProvider($this->settingsProvider, $this->stateProvider);
    }

    /**
     * @dataProvider exclusionProvider
     */
    public function testIsEntityExcluded(
        bool $expected,
        bool $isChannelEntity,
        array $dependentDataMap = [],
        array $enabledEntityMap = []
    ) {
        $isDependentOnChannelEntity = count($dependentDataMap) > 1
            || self::TEST_ENTITY_NAME !== $dependentDataMap[0][0]
            || [] !== $dependentDataMap[0][1];

        $this->settingsProvider->expects(self::once())
            ->method('isChannelEntity')
            ->with(self::TEST_ENTITY_NAME)
            ->willReturn($isChannelEntity);
        $this->settingsProvider->expects(self::any())
            ->method('isDependentOnChannelEntity')
            ->with(self::TEST_ENTITY_NAME)
            ->willReturn($isDependentOnChannelEntity);
        $this->settingsProvider->expects(self::any())
            ->method('getDependentEntities')
            ->willReturnMap($dependentDataMap);
        $this->stateProvider->expects(self::any())
            ->method('isEntityEnabled')
            ->willReturnMap($enabledEntityMap);

        self::assertSame($expected, $this->exclusionProvider->isIgnoredEntity(self::TEST_ENTITY_NAME));
    }

    /**
     * @dataProvider exclusionProvider
     */
    public function testIsRelationExcluded(
        bool $expected,
        bool $isChannelEntity,
        array $dependentDataMap = [],
        array $enabledEntityMap = []
    ) {
        $this->settingsProvider->expects(self::any())
            ->method('isChannelEntity')
            ->willReturnMap([
                [self::TEST_MAIN_ENTITY_NAME, true],
                [self::TEST_ENTITY_NAME, $isChannelEntity]
            ]);
        $this->settingsProvider->expects(self::any())
            ->method('getDependentEntities')
            ->willReturnMap($dependentDataMap);
        $this->stateProvider->expects(self::any())
            ->method('isEntityEnabled')
            ->willReturnMap($enabledEntityMap);

        $classMetadataMock = $this->createMock(ClassMetadata::class);
        $classMetadataMock->expects(self::any())
            ->method('getName')
            ->willReturn(self::TEST_MAIN_ENTITY_NAME);
        $classMetadataMock->expects(self::any())
            ->method('getAssociationTargetClass')
            ->with(self::TEST_ASSOC_NAME)
            ->willReturn(self::TEST_ENTITY_NAME);

        self::assertSame(
            $expected,
            $this->exclusionProvider->isIgnoredRelation($classMetadataMock, self::TEST_ASSOC_NAME)
        );
    }

    public function testIsFieldExcluded()
    {
        $classMetadataMock = $this->createMock(ClassMetadata::class);

        self::assertFalse(
            $this->exclusionProvider->isIgnoredField($classMetadataMock, self::TEST_FIELD_NAME),
            'should not exclude any fields'
        );
    }

    /**
     * @return array
     */
    public function exclusionProvider()
    {
        return [
            'not related to channel entity given, should not be skipped' => [
                false,
                false,
                [[self::TEST_ENTITY_NAME, []]],
                [[self::TEST_MAIN_ENTITY_NAME, true]]
            ],
            'channel entity given, but not enabled'                      => [
                true,
                true,
                [[self::TEST_ENTITY_NAME, []]],
                [[self::TEST_ENTITY_NAME, false], [self::TEST_MAIN_ENTITY_NAME, true]]
            ],
            'channel entity given, enabled entity should not be skipped' => [
                false,
                true,
                [[self::TEST_ENTITY_NAME, []]],
                [[self::TEST_ENTITY_NAME, true], [self::TEST_MAIN_ENTITY_NAME, true]]
            ],
            'dependent entity given, all dependencies disabled'          => [
                true,
                false,
                [
                    [
                        self::TEST_ENTITY_NAME,
                        [self::TEST_DEPENDENCY_ENTITY_NAME, self::TEST_ANOTHER_DEPENDENCY_ENTITY_NAME]
                    ]
                ],
                [
                    [self::TEST_DEPENDENCY_ENTITY_NAME, false],
                    [self::TEST_ANOTHER_DEPENDENCY_ENTITY_NAME, false],
                    [self::TEST_ENTITY_NAME, true]
                ]
            ],
            'dependent entity given, one dependency disabled'            => [
                false,
                false,
                [
                    [
                        self::TEST_ENTITY_NAME,
                        [self::TEST_DEPENDENCY_ENTITY_NAME, self::TEST_ANOTHER_DEPENDENCY_ENTITY_NAME]
                    ]
                ],
                [
                    [self::TEST_DEPENDENCY_ENTITY_NAME, true],
                    [self::TEST_ANOTHER_DEPENDENCY_ENTITY_NAME, false],
                    [self::TEST_MAIN_ENTITY_NAME, true]
                ]
            ],
        ];
    }
}
