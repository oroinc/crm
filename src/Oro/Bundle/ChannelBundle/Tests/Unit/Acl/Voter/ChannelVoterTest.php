<?php

namespace Oro\Bundle\ChannelBundle\Tests\Unit\Acl\Voter;

use Oro\Bundle\ChannelBundle\Acl\Voter\ChannelVoter;
use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\ChannelBundle\Provider\SettingsProvider;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class ChannelVoterTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ChannelVoter
     */
    protected $voter;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|DoctrineHelper
     */
    protected $doctrineHelper;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|SettingsProvider
     */
    protected $settingsProvider;

    protected function setUp()
    {
        $this->doctrineHelper = $this->getMockBuilder('Oro\Bundle\EntityBundle\ORM\DoctrineHelper')
            ->disableOriginalConstructor()
            ->getMock();

        $this->settingsProvider = $this->getMockBuilder('Oro\Bundle\ChannelBundle\Provider\SettingsProvider')
            ->disableOriginalConstructor()
            ->getMock();

        $this->voter = new ChannelVoter($this->doctrineHelper);
        $this->voter->setSettingsProvider($this->settingsProvider);
    }

    protected function tearDown()
    {
        unset($this->voter, $this->doctrineHelper);
    }

    /**
     * @param object $object
     * @param string $className
     * @param array $attributes
     * @param bool $isChannelSystem
     * @param bool $expected
     *
     * @dataProvider attributesDataProvider
     */
    public function testVote($object, $className, $attributes, $isChannelSystem, $expected)
    {
        $this->doctrineHelper->expects($this->any())
            ->method('getEntityClass')
            ->with($object)
            ->will($this->returnValue($className));

        $this->voter->setClassName('Oro\Bundle\ChannelBundle\Entity\Channel');

        $this->doctrineHelper->expects($this->any())
            ->method('getSingleEntityIdentifier')
            ->with($object, false)
            ->will($this->returnValue(1));

        $this->settingsProvider->expects($this->any())
            ->method('isChannelSystem')
            ->will($this->returnValue($isChannelSystem));

        /** @var TokenInterface $token */
        $token = $this->createMock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
        $this->assertEquals(
            $expected,
            $this->voter->vote($token, $object, $attributes)
        );
    }

    /**
     * @return array
     */
    public function attributesDataProvider()
    {
        $className = 'Oro\Bundle\ChannelBundle\Entity\Channel';

        return [
            // channel system
            [$this->getChannel(), $className, ['VIEW'], false, ChannelVoter::ACCESS_ABSTAIN],
            [$this->getChannel(), $className, ['CREATE'], false, ChannelVoter::ACCESS_ABSTAIN],
            [$this->getChannel(), $className, ['EDIT'], false, ChannelVoter::ACCESS_ABSTAIN],
            [$this->getChannel(), $className, ['DELETE'], false, ChannelVoter::ACCESS_ABSTAIN],
            [$this->getChannel(), $className, ['ASSIGN'], false, ChannelVoter::ACCESS_ABSTAIN],
            // channel non system
            [$this->getChannel(), $className, ['VIEW'], true, ChannelVoter::ACCESS_ABSTAIN],
            [$this->getChannel(), $className, ['CREATE'], true, ChannelVoter::ACCESS_DENIED],
            [$this->getChannel(), $className, ['EDIT'], true, ChannelVoter::ACCESS_ABSTAIN],
            [$this->getChannel(), $className, ['DELETE'], true, ChannelVoter::ACCESS_DENIED],
            [$this->getChannel(), $className, ['ASSIGN'], true, ChannelVoter::ACCESS_ABSTAIN],
        ];
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|Channel
     */
    protected function getChannel()
    {
        return $this->createMock('Oro\Bundle\ChannelBundle\Entity\Channel');
    }
}
