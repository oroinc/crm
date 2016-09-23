<?php

namespace Oro\Bundle\ChannelBundle\Tests\Unit\Provider;

use Oro\Bundle\ChannelBundle\Provider\StateProvider;
use Oro\Bundle\ChannelBundle\Provider\SettingsProvider;
use Oro\Bundle\ChannelBundle\Provider\EntityExclusionProvider;

class EntityExclusionProviderTest extends \PHPUnit_Framework_TestCase
{
    const TEST_MAIN_ENTITY_NAME               = 'TestBundle\Entity\TestMain';
    const TEST_ENTITY_NAME                    = 'TestBundle\Entity\Test';
    const TEST_DEPENDENCY_ENTITY_NAME         = 'TestBundle\Entity\Test1';
    const TEST_ANOTHER_DEPENDENCY_ENTITY_NAME = 'TestBundle\Entity\Test12';
    const TEST_ASSOC_NAME                     = 'relation';
    const TEST_FIELD_NAME                     = 'field';

    /** @var EntityExclusionProvider */
    protected $exclusionProvider;

    /** @var SettingsProvider|\PHPUnit_Framework_MockObject_MockObject */
    protected $settingsProvider;

    /** @var StateProvider|\PHPUnit_Framework_MockObject_MockObject */
    protected $stateProvider;

    public function setUp()
    {
        $this->settingsProvider = $this->getMockBuilder('Oro\Bundle\ChannelBundle\Provider\SettingsProvider')
            ->disableOriginalConstructor()
            ->setMethods(['isChannelEntity', 'getDependentEntityData'])
            ->getMock();
        $this->stateProvider    = $this->getMockBuilder('Oro\Bundle\ChannelBundle\Provider\StateProvider')
            ->disableOriginalConstructor()->getMock();

        $this->exclusionProvider = new EntityExclusionProvider($this->settingsProvider, $this->stateProvider);
    }

    public function tearDown()
    {
        unset($this->exclusionProvider, $this->stateProvider, $this->settingsProvider);
    }

    /**
     * @dataProvider exclusionProvider
     *
     * @param bool  $expected
     * @param bool  $isChannelEntity
     * @param array $dependentDataMap
     * @param array $enabledEntityMap
     */
    public function testIsEntityExcluded(
        $expected,
        $isChannelEntity,
        $dependentDataMap = [],
        $enabledEntityMap = []
    ) {
        $this->settingsProvider->expects($this->any())
            ->method('isChannelEntity')->with($this->equalTo(self::TEST_ENTITY_NAME))
            ->will($this->returnValue($isChannelEntity));
        $this->settingsProvider->expects($this->any())
            ->method('getDependentEntityData')
            ->will($this->returnValueMap($dependentDataMap));
        $this->stateProvider->expects($this->any())
            ->method('isEntityEnabled')
            ->will($this->returnValueMap($enabledEntityMap));

        $this->assertSame($expected, $this->exclusionProvider->isIgnoredEntity(self::TEST_ENTITY_NAME));
    }

    /**
     * @dataProvider exclusionProvider
     *
     * @param bool  $expected
     * @param bool  $isChannelEntity
     * @param array $dependentDataMap
     * @param array $enabledEntityMap
     */
    public function testIsRelationExcluded(
        $expected,
        $isChannelEntity,
        $dependentDataMap = [],
        $enabledEntityMap = []
    ) {
        $this->settingsProvider->expects($this->any())
            ->method('isChannelEntity')
            ->will(
                $this->returnValueMap(
                    [
                        [self::TEST_MAIN_ENTITY_NAME, true],
                        [self::TEST_ENTITY_NAME, $isChannelEntity]
                    ]
                )
            );
        $this->settingsProvider->expects($this->any())
            ->method('getDependentEntityData')
            ->will($this->returnValueMap($dependentDataMap));
        $this->stateProvider->expects($this->any())
            ->method('isEntityEnabled')
            ->will($this->returnValueMap($enabledEntityMap));

        $classMetadataMock = $this->getMockBuilder('Doctrine\ORM\Mapping\ClassMetadata')
            ->disableOriginalConstructor()->getMock();
        $classMetadataMock->expects($this->once())
            ->method('getName')
            ->will($this->returnValue(self::TEST_MAIN_ENTITY_NAME));
        $classMetadataMock->expects($this->once())
            ->method('getAssociationTargetClass')->with($this->equalTo(self::TEST_ASSOC_NAME))
            ->will($this->returnValue(self::TEST_ENTITY_NAME));

        $this->assertSame(
            $expected,
            $this->exclusionProvider->isIgnoredRelation($classMetadataMock, self::TEST_ASSOC_NAME)
        );
    }

    public function testIsFieldExcluded()
    {
        $classMetadataMock = $this->getMockBuilder('Doctrine\ORM\Mapping\ClassMetadata')
            ->disableOriginalConstructor()->getMock();

        $this->assertFalse(
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
                [[self::TEST_ENTITY_NAME, false]],
                [[self::TEST_MAIN_ENTITY_NAME, true]]
            ],
            'channel entity given, but not enabled'                      => [
                true,
                true,
                [[self::TEST_ENTITY_NAME, false]],
                [[self::TEST_ENTITY_NAME, false], [self::TEST_MAIN_ENTITY_NAME, true]]
            ],
            'channel entity given, enabled entity should not be skipped' => [
                false,
                true,
                [[self::TEST_ENTITY_NAME, false]],
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
