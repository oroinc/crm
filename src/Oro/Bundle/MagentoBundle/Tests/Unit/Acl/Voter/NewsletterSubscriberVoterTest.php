<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Acl\Voter;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\MagentoBundle\Acl\Voter\NewsletterSubscriberVoter;
use Oro\Bundle\MagentoBundle\Entity\Customer;
use Oro\Bundle\MagentoBundle\Entity\NewsletterSubscriber;
use Oro\Bundle\MagentoBundle\Model\ChannelSettingsProvider;
use Symfony\Component\Security\Acl\Model\ObjectIdentityInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class NewsletterSubscriberVoterTest extends \PHPUnit\Framework\TestCase
{
    /** @var \PHPUnit\Framework\MockObject\MockObject|DoctrineHelper */
    private $doctrineHelper;

    /** @var \PHPUnit\Framework\MockObject\MockObject|ChannelSettingsProvider */
    private $settingsProvider;

    /** @var NewsletterSubscriberVoter */
    private $voter;

    protected function setUp(): void
    {
        $this->doctrineHelper = $this->createMock(DoctrineHelper::class);
        $this->settingsProvider = $this->createMock(ChannelSettingsProvider::class);
        $this->voter = new NewsletterSubscriberVoter($this->doctrineHelper);
        $this->voter->setSettingsProvider($this->settingsProvider);
    }

    /**
     * @dataProvider attributesDataProvider
     */
    public function testVote($object, $attributes, $hasApplicableChannels, $isChannelApplicable, $expected)
    {
        $this->voter->setClassName(NewsletterSubscriber::class);

        $this->doctrineHelper->expects($this->any())
            ->method('getSingleEntityIdentifier')
            ->with($object, false)
            ->will($this->returnValue(1));

        $this->settingsProvider->expects($this->any())
            ->method('isChannelApplicable')
            ->will($this->returnValue($isChannelApplicable));

        $this->settingsProvider->expects($this->any())
            ->method('hasApplicableChannels')
            ->will($this->returnValue($hasApplicableChannels));

        $token = $this->createMock(TokenInterface::class);
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
        $oid = $this->getObjectIdentity(NewsletterSubscriber::class);

        return [
            // has not applicable channels
            [$oid, ['VIEW'], false, false, NewsletterSubscriberVoter::ACCESS_ABSTAIN],
            [$oid, ['CREATE'], false, false, NewsletterSubscriberVoter::ACCESS_DENIED],
            [$oid, ['EDIT'], false, false, NewsletterSubscriberVoter::ACCESS_DENIED],
            [$oid, ['DELETE'], false, false, NewsletterSubscriberVoter::ACCESS_ABSTAIN],
            [$oid, ['ASSIGN'], false, false, NewsletterSubscriberVoter::ACCESS_ABSTAIN],
            // channel not applicable
            [$this->getSubscriber(), ['VIEW'], true, false, NewsletterSubscriberVoter::ACCESS_ABSTAIN],
            [$this->getSubscriber(), ['CREATE'], true, false, NewsletterSubscriberVoter::ACCESS_DENIED],
            [$this->getSubscriber(), ['EDIT'], true, false, NewsletterSubscriberVoter::ACCESS_DENIED],
            [$this->getSubscriber(), ['DELETE'], true, false, NewsletterSubscriberVoter::ACCESS_ABSTAIN],
            [$this->getSubscriber(), ['ASSIGN'], true, false, NewsletterSubscriberVoter::ACCESS_ABSTAIN],
            // without customer
            [$this->getSubscriber(), ['VIEW'], true, true, NewsletterSubscriberVoter::ACCESS_ABSTAIN],
            [$this->getSubscriber(), ['CREATE'], true, true, NewsletterSubscriberVoter::ACCESS_ABSTAIN],
            [$this->getSubscriber(), ['EDIT'], true, true, NewsletterSubscriberVoter::ACCESS_ABSTAIN],
            [$this->getSubscriber(), ['DELETE'], true, true, NewsletterSubscriberVoter::ACCESS_ABSTAIN],
            [$this->getSubscriber(), ['ASSIGN'], true, true, NewsletterSubscriberVoter::ACCESS_ABSTAIN],
            // with customer and without origin id
            [$this->getSubscriber(true), ['VIEW'], true, true, NewsletterSubscriberVoter::ACCESS_ABSTAIN],
            [$this->getSubscriber(true), ['CREATE'], true, true, NewsletterSubscriberVoter::ACCESS_DENIED],
            [$this->getSubscriber(true), ['EDIT'], true, true, NewsletterSubscriberVoter::ACCESS_DENIED],
            [$this->getSubscriber(true), ['DELETE'], true, true, NewsletterSubscriberVoter::ACCESS_ABSTAIN],
            [$this->getSubscriber(true), ['ASSIGN'], true, true, NewsletterSubscriberVoter::ACCESS_ABSTAIN],
            // applicable with origin id
            [$this->getSubscriber(true, 1), ['VIEW'], true, true, NewsletterSubscriberVoter::ACCESS_ABSTAIN],
            [$this->getSubscriber(true, 1), ['CREATE'], true, true, NewsletterSubscriberVoter::ACCESS_ABSTAIN],
            [$this->getSubscriber(true, 1), ['EDIT'], true, true, NewsletterSubscriberVoter::ACCESS_ABSTAIN],
            [$this->getSubscriber(true, 1), ['DELETE'], true, true, NewsletterSubscriberVoter::ACCESS_ABSTAIN],
            [$this->getSubscriber(true, 1), ['ASSIGN'], true, true, NewsletterSubscriberVoter::ACCESS_ABSTAIN],
            // applicable but without channels
            [$this->getSubscriber(1), ['VIEW'], false, true, NewsletterSubscriberVoter::ACCESS_ABSTAIN],
            [$this->getSubscriber(1), ['CREATE'], false, true, NewsletterSubscriberVoter::ACCESS_DENIED],
            [$this->getSubscriber(1), ['EDIT'], false, true, NewsletterSubscriberVoter::ACCESS_DENIED],
            [$this->getSubscriber(1), ['DELETE'], false, true, NewsletterSubscriberVoter::ACCESS_ABSTAIN],
            [$this->getSubscriber(1), ['ASSIGN'], false, true, NewsletterSubscriberVoter::ACCESS_ABSTAIN]
        ];
    }

    /**
     * @param string $objectIdentityClass
     * @param string $className
     *
     * @return ObjectIdentityInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private function getObjectIdentity($className)
    {
        $objectIdentity = $this->createMock(ObjectIdentityInterface::class);
        $objectIdentity->expects($this->any())
            ->method('getType')
            ->will($this->returnValue($className));

        return $objectIdentity;
    }

    /**
     * @param bool $hasCustomer
     * @param int $customerOriginId
     *
     * @return NewsletterSubscriber
     */
    private function getSubscriber($hasCustomer = false, $customerOriginId = null)
    {
        $newsletterSubscriber = new NewsletterSubscriber();
        $newsletterSubscriber->setChannel(new Channel());
        if ($hasCustomer) {
            $customer = new Customer();
            $newsletterSubscriber->setCustomer($customer);
            if ($customerOriginId) {
                $customer->setOriginId($customerOriginId);
            }
        }

        return $newsletterSubscriber;
    }
}
