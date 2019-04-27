<?php

namespace Oro\Bundle\ChannelBundle\Tests\Unit\Acl\Voter;

use Oro\Bundle\ChannelBundle\Acl\Voter\ChannelVoter;
use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\ChannelBundle\Provider\SettingsProvider;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class ChannelVoterTest extends \PHPUnit\Framework\TestCase
{
    /** @var \PHPUnit\Framework\MockObject\MockObject|DoctrineHelper */
    private $doctrineHelper;

    /** @var \PHPUnit\Framework\MockObject\MockObject|SettingsProvider */
    private $settingsProvider;

    /** @var ChannelVoter */
    private $voter;

    protected function setUp()
    {
        $this->doctrineHelper = $this->createMock(DoctrineHelper::class);
        $this->settingsProvider = $this->createMock(SettingsProvider::class);

        $this->voter = new ChannelVoter($this->doctrineHelper);
        $this->voter->setSettingsProvider($this->settingsProvider);
    }

    /**
     * @param object $object
     * @param string $className
     * @param array  $attributes
     * @param bool   $isSystemChannel
     * @param bool   $expected
     *
     * @dataProvider attributesDataProvider
     */
    public function testVote($object, $className, $attributes, $isSystemChannel, $expected)
    {
        $this->doctrineHelper->expects(self::any())
            ->method('getEntityClass')
            ->with($object)
            ->willReturn($className);

        $this->doctrineHelper->expects(self::any())
            ->method('getSingleEntityIdentifier')
            ->with($object, false)
            ->willReturn(1);

        $this->settingsProvider->expects(self::any())
            ->method('isSystemChannel')
            ->willReturn($isSystemChannel);

        /** @var TokenInterface $token */
        $token = $this->createMock(TokenInterface::class);

        $this->voter->setClassName(Channel::class);

        self::assertEquals(
            $expected,
            $this->voter->vote($token, $object, $attributes)
        );
    }

    /**
     * @return array
     */
    public function attributesDataProvider()
    {
        $className = Channel::class;

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
    private function getChannel()
    {
        $channel = $this->createMock(Channel::class);
        $channel->expects(self::any())
            ->method('getChannelType')
            ->willReturn('test_channel');

        return $channel;
    }
}
