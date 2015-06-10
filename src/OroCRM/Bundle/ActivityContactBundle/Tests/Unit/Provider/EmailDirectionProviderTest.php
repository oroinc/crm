<?php

namespace OroCRM\Bundle\ActivityContactBundle\Tests\Unit\Provider;

use Oro\Bundle\EmailBundle\Entity\Email;
use Oro\Bundle\EmailBundle\Tests\Unit\Entity\TestFixtures\EmailAddress;
use Oro\Bundle\EmailBundle\Tests\Unit\Fixtures\Entity\TestEmailHolder;

use OroCRM\Bundle\ActivityContactBundle\Direction\DirectionProviderInterface;
use OroCRM\Bundle\ActivityContactBundle\Provider\EmailDirectionProvider;

class EmailDirectionProviderTest extends \PHPUnit_Framework_TestCase
{
    /** @var EmailDirectionProvider */
    protected $provider;

    public function setUp()
    {
        $this->provider = new EmailDirectionProvider();
    }

    public function testGetSupportedClass()
    {
        $this->assertEquals('Oro\Bundle\EmailBundle\Entity\Email', $this->provider->getSupportedClass());
    }

    public function testGetDirection()
    {
        $fromEmail = 'from@example.com';
        $toEmail   = 'to@example.com';

        $sender  = new TestEmailHolder($fromEmail);
        $reciver = new TestEmailHolder($toEmail);
        $email   = new Email();

        $emailAddress = new EmailAddress();
        $emailAddress->setEmail($fromEmail);
        $email->setFromEmailAddress($emailAddress);

        $this->assertEquals(
            DirectionProviderInterface::DIRECTION_INCOMING,
            $this->provider->getDirection($email, $reciver)
        );
        $this->assertEquals(
            DirectionProviderInterface::DIRECTION_OUTGOING,
            $this->provider->getDirection($email, $sender)
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
