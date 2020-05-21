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

    protected function setUp(): void
    {
        $this->doctrineHelper = $this->createMock(DoctrineHelper::class);
        $this->settingsProvider = $this->createMock(SettingsProvider::class);

        $this->voter = new ChannelVoter($this->doctrineHelper);
        $this->voter->setSettingsProvider($this->settingsProvider);
    }

    /**
     * @dataProvider attributesDataProvider
     */
    public function testVote($object, $attributes, $isSystemChannel, $expected)
    {
        $this->voter->setClassName(Channel::class);

        $this->doctrineHelper->expects(self::any())
            ->method('getSingleEntityIdentifier')
            ->with($object, false)
            ->willReturn(1);

        $this->settingsProvider->expects(self::any())
            ->method('isSystemChannel')
            ->willReturn($isSystemChannel);

        $token = $this->createMock(TokenInterface::class);
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
        return [
            // channel system
            [$this->getChannel(), ['VIEW'], false, ChannelVoter::ACCESS_ABSTAIN],
            [$this->getChannel(), ['CREATE'], false, ChannelVoter::ACCESS_ABSTAIN],
            [$this->getChannel(), ['EDIT'], false, ChannelVoter::ACCESS_ABSTAIN],
            [$this->getChannel(), ['DELETE'], false, ChannelVoter::ACCESS_ABSTAIN],
            [$this->getChannel(), ['ASSIGN'], false, ChannelVoter::ACCESS_ABSTAIN],
            // channel non system
            [$this->getChannel(), ['VIEW'], true, ChannelVoter::ACCESS_ABSTAIN],
            [$this->getChannel(), ['CREATE'], true, ChannelVoter::ACCESS_DENIED],
            [$this->getChannel(), ['EDIT'], true, ChannelVoter::ACCESS_ABSTAIN],
            [$this->getChannel(), ['DELETE'], true, ChannelVoter::ACCESS_DENIED],
            [$this->getChannel(), ['ASSIGN'], true, ChannelVoter::ACCESS_ABSTAIN]
        ];
    }

    /**
     * @return Channel
     */
    private function getChannel()
    {
        $channel = new Channel();
        $channel->setChannelType('test_channel');

        return $channel;
    }
}
