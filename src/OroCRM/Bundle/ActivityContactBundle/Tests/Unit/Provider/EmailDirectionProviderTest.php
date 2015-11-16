<?php

namespace OroCRM\Bundle\ActivityContactBundle\Tests\Unit\Provider;

use Doctrine\Common\Inflector\Inflector;

use Oro\Bundle\EmailBundle\Entity\Email;
use Oro\Bundle\EmailBundle\Entity\EmailRecipient;
use Oro\Bundle\EmailBundle\Tests\Unit\Entity\TestFixtures\EmailAddress;
use Oro\Bundle\EmailBundle\Tests\Unit\Fixtures\Entity\TestEmailHolder;

use OroCRM\Bundle\ActivityContactBundle\Direction\DirectionProviderInterface;
use OroCRM\Bundle\ActivityContactBundle\Provider\EmailDirectionProvider;
use OroCRM\Bundle\MarketingListBundle\Provider\ContactInformationFieldsProvider;

class EmailDirectionProviderTest extends \PHPUnit_Framework_TestCase
{
    const FROM_EMAIL = 'from@example.com';
    const TO_EMAIL = 'to@example.com';
    const COLUMN_NAME = 'test_column';

    /** @var EmailDirectionProvider */
    protected $provider;

    public function setUp()
    {
        $fieldConfigurationMock = $this->getMockBuilder('Oro\Bundle\EntityConfigBundle\Config\Config')
            ->disableOriginalConstructor()
            ->getMock();
        $fieldConfigurationMock->method('get')
            ->with('contact_information')
            ->will($this->returnValue(ContactInformationFieldsProvider::CONTACT_INFORMATION_SCOPE_EMAIL));

        $configProvider = $this->getMockBuilder('Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider')
            ->disableOriginalConstructor()
            ->getMock();
        $configProvider->method('hasConfig')
            ->withAnyParameters()
            ->will($this->returnValue(true));
        $configProvider->method('getConfig')
            ->withAnyParameters()
            ->will($this->returnValue($fieldConfigurationMock));

        $metadataMock = $this->getMockBuilder('Doctrine\ORM\Mapping\ClassMetadata')
            ->disableOriginalConstructor()
            ->getMock();
        $metadataMock->method('getColumnNames')
            ->withAnyParameters()
            ->will($this->returnValue(array(self::COLUMN_NAME)));

        $doctrineHelper = $this->getMockBuilder('Oro\Bundle\EntityBundle\ORM\DoctrineHelper')
            ->disableOriginalConstructor()
            ->getMock();
        $doctrineHelper->method('getEntityMetadata')
            ->withAnyParameters()
            ->will($this->returnValue($metadataMock));

        $emailHolderHelper = $this->getMockBuilder('Oro\Bundle\EmailBundle\Tools\EmailHolderHelper')
            ->disableOriginalConstructor()
            ->getMock();

        $this->provider = new EmailDirectionProvider($configProvider, $doctrineHelper, $emailHolderHelper);
    }

    public function testGetSupportedClass()
    {
        $this->assertEquals('Oro\Bundle\EmailBundle\Entity\Email', $this->provider->getSupportedClass());
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
        $getMethodName = "get" . Inflector::classify(self::COLUMN_NAME);
        $target = $this->getMock('Extend\Entity\Test', array($getMethodName));
        $target->method($getMethodName)
            ->will($this->returnValue(self::FROM_EMAIL));

        $email   = new Email();

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
        $getMethodName = "get" . Inflector::classify(self::COLUMN_NAME);
        $target = $this->getMock('Extend\Entity\Test', array($getMethodName));
        $target->method($getMethodName)
            ->will($this->returnValue(self::TO_EMAIL));

        $email   = new Email();

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
        $getMethodName = "get" . Inflector::classify(self::COLUMN_NAME);
        $target = $this->getMock('Extend\Entity\Test', array($getMethodName));
        $target->method($getMethodName)
            ->will($this->returnValue('test' . self::TO_EMAIL));

        $email   = new Email();

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
