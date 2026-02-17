<?php

namespace Oro\Bundle\ChannelBundle\Tests\Unit\Acl\Voter;

use Oro\Bundle\ChannelBundle\Acl\Voter\ChannelVoter;
use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\ChannelBundle\Provider\SettingsProvider;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Component\Testing\Unit\TestContainerBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class ChannelVoterTest extends TestCase
{
    private DoctrineHelper&MockObject $doctrineHelper;
    private SettingsProvider&MockObject $settingsProvider;
    private ChannelVoter $voter;

    #[\Override]
    protected function setUp(): void
    {
        $this->doctrineHelper = $this->createMock(DoctrineHelper::class);
        $this->settingsProvider = $this->createMock(SettingsProvider::class);

        $container = TestContainerBuilder::create()
            ->add(SettingsProvider::class, $this->settingsProvider)
            ->getContainer($this);

        $this->voter = new ChannelVoter($this->doctrineHelper, $container);
    }

    /**
     * @dataProvider attributesDataProvider
     */
    public function testVote(Channel $object, array $attributes, bool $isSystemChannel, int $expected): void
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

    public function attributesDataProvider(): array
    {
        return [
            // channel system
            [$this->getChannel(), ['VIEW'], false, VoterInterface::ACCESS_ABSTAIN],
            [$this->getChannel(), ['CREATE'], false, VoterInterface::ACCESS_ABSTAIN],
            [$this->getChannel(), ['EDIT'], false, VoterInterface::ACCESS_ABSTAIN],
            [$this->getChannel(), ['DELETE'], false, VoterInterface::ACCESS_ABSTAIN],
            [$this->getChannel(), ['ASSIGN'], false, VoterInterface::ACCESS_ABSTAIN],
            // channel non system
            [$this->getChannel(), ['VIEW'], true, VoterInterface::ACCESS_ABSTAIN],
            [$this->getChannel(), ['CREATE'], true, VoterInterface::ACCESS_DENIED],
            [$this->getChannel(), ['EDIT'], true, VoterInterface::ACCESS_ABSTAIN],
            [$this->getChannel(), ['DELETE'], true, VoterInterface::ACCESS_DENIED],
            [$this->getChannel(), ['ASSIGN'], true, VoterInterface::ACCESS_ABSTAIN]
        ];
    }

    private function getChannel(): Channel
    {
        $channel = new Channel();
        $channel->setChannelType('test_channel');

        return $channel;
    }
}
