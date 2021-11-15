<?php

namespace Oro\Bundle\ActivityContactBundle\Tests\Unit\Provider;

use Doctrine\Inflector\Inflector;
use Doctrine\Inflector\Rules\English\InflectorFactory;
use Doctrine\ORM\Mapping\ClassMetadata;
use Oro\Bundle\ActivityContactBundle\Direction\DirectionProviderInterface;
use Oro\Bundle\ActivityContactBundle\Provider\EmailDirectionProvider;
use Oro\Bundle\EmailBundle\Entity\Email;
use Oro\Bundle\EmailBundle\Entity\EmailRecipient;
use Oro\Bundle\EmailBundle\Tests\Unit\Entity\TestFixtures\EmailAddress;
use Oro\Bundle\EmailBundle\Tests\Unit\Fixtures\Entity\TestEmailHolder;
use Oro\Bundle\EmailBundle\Tools\EmailHolderHelper;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\EntityConfigBundle\Config\Config;
use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;

class EmailDirectionProviderTest extends \PHPUnit\Framework\TestCase
{
    private const FROM_EMAIL = 'from@example.com';
    private const TO_EMAIL = 'to@example.com';
    private const COLUMN_NAME = 'test_column';

    private EmailDirectionProvider $provider;
    private Inflector $inflector;

    protected function setUp(): void
    {
        $fieldConfiguration = $this->createMock(Config::class);
        $fieldConfiguration->expects(self::any())
            ->method('get')
            ->with('contact_information')
            ->willReturn(DirectionProviderInterface::CONTACT_INFORMATION_SCOPE_EMAIL);

        $configProvider = $this->createMock(ConfigProvider::class);
        $configProvider->expects(self::any())
            ->method('hasConfig')
            ->withAnyParameters()
            ->willReturn(true);
        $configProvider->expects(self::any())
            ->method('getConfig')
            ->withAnyParameters()
            ->willReturn($fieldConfiguration);

        $metadata = $this->createMock(ClassMetadata::class);
        $metadata->expects(self::any())
            ->method('getColumnNames')
            ->withAnyParameters()
            ->willReturn([self::COLUMN_NAME]);

        $doctrineHelper = $this->createMock(DoctrineHelper::class);
        $doctrineHelper->expects(self::any())
            ->method('getEntityMetadata')
            ->withAnyParameters()
            ->willReturn($metadata);

        $emailHolderHelper = $this->createMock(EmailHolderHelper::class);

        $this->inflector = (new InflectorFactory())->build();
        $this->provider = new EmailDirectionProvider(
            $configProvider,
            $doctrineHelper,
            $emailHolderHelper,
            $this->inflector
        );
    }

    public function testGetDirection()
    {
        $sender  = new TestEmailHolder(self::FROM_EMAIL);
        $recipient = new TestEmailHolder(self::TO_EMAIL);
        $email   = new Email();

        $emailAddress = new EmailAddress();
        $emailAddress->setEmail(self::FROM_EMAIL);
        $email->setFromEmailAddress($emailAddress);

        $this->assertEquals(
            DirectionProviderInterface::DIRECTION_INCOMING,
            $this->provider->getDirection($email, $recipient)
        );
        $this->assertEquals(
            DirectionProviderInterface::DIRECTION_OUTGOING,
            $this->provider->getDirection($email, $sender)
        );
    }

    public function testOutgoingDirectionForCustomEntity()
    {
        $getMethodName = 'get' . $this->inflector->classify(self::COLUMN_NAME);
        $target = $this->getMockBuilder(\ArrayObject::class)
            ->addMethods([$getMethodName])
            ->getMock();
        $target->expects(self::once())
            ->method($getMethodName)
            ->willReturn(self::FROM_EMAIL);

        $email = new Email();

        $emailAddress = new EmailAddress();
        $emailAddress->setEmail(self::FROM_EMAIL);
        $email->setFromEmailAddress($emailAddress);

        $this->assertEquals(
            DirectionProviderInterface::DIRECTION_OUTGOING,
            $this->provider->getDirection($email, $target)
        );
    }

    public function testIncomingDirectionForCustomEntity()
    {
        $getMethodName = 'get' . $this->inflector->classify(self::COLUMN_NAME);
        $target = $this->getMockBuilder(\ArrayObject::class)
            ->addMethods([$getMethodName])
            ->getMock();
        $target->expects(self::atLeastOnce())
            ->method($getMethodName)
            ->willReturn(self::TO_EMAIL);

        $email = new Email();

        $toEmailAddress = new EmailAddress();
        $toEmailAddress->setEmail(self::TO_EMAIL);
        $recipient = new EmailRecipient();
        $recipient->setEmailAddress($toEmailAddress)->setType(EmailRecipient::TO);
        $email->addRecipient($recipient);

        $fromEmailAddress = new EmailAddress();
        $fromEmailAddress->setEmail(self::FROM_EMAIL);
        $email->setFromEmailAddress($fromEmailAddress);

        $this->assertEquals(
            DirectionProviderInterface::DIRECTION_INCOMING,
            $this->provider->getDirection($email, $target)
        );
    }

    public function testUnknownDirectionForCustomEntity()
    {
        $getMethodName = 'get' . $this->inflector->classify(self::COLUMN_NAME);
        $target = $this->getMockBuilder(\ArrayObject::class)
            ->addMethods([$getMethodName])
            ->getMock();
        $target->expects(self::atLeastOnce())
            ->method($getMethodName)
            ->willReturn('test' . self::TO_EMAIL);

        $email = new Email();

        $toEmailAddress = new EmailAddress();
        $toEmailAddress->setEmail(self::TO_EMAIL);
        $recipient = new EmailRecipient();
        $recipient->setEmailAddress($toEmailAddress)->setType(EmailRecipient::TO);
        $email->addRecipient($recipient);

        $fromEmailAddress = new EmailAddress();
        $fromEmailAddress->setEmail(self::FROM_EMAIL);
        $email->setFromEmailAddress($fromEmailAddress);

        $this->assertEquals(
            DirectionProviderInterface::DIRECTION_UNKNOWN,
            $this->provider->getDirection($email, $target)
        );
    }

    public function testGetDate()
    {
        $email = new Email();
        $date  = new \DateTime('now', new \DateTimeZone('UTC'));
        $email->setSentAt($date);
        $this->assertSame($date, $this->provider->getDate($email));
    }
}
