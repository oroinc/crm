<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\Acl\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

use OroCRM\Bundle\MagentoBundle\Entity\NewsletterSubscriber;
use OroCRM\Bundle\MagentoBundle\Acl\Voter\NewsletterSubscriberVoter;

class NewsletterSubscriberVoterTest extends AbstractTwoWaySyncVoterTest
{
    /**
     * @var NewsletterSubscriberVoter
     */
    protected $voter;

    protected function setUp()
    {
        parent::setUp();

        $this->voter = new NewsletterSubscriberVoter($this->doctrineHelper);
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
     * @param bool $hasApplicableChannels
     * @param bool $isChannelApplicable
     * @param bool $expected
     *
     * @dataProvider attributesDataProvider
     */
    public function testVote($object, $className, $attributes, $hasApplicableChannels, $isChannelApplicable, $expected)
    {
        $this->doctrineHelper->expects($this->any())
            ->method('getEntityClass')
            ->with($object)
            ->will($this->returnValue($className));

        $this->voter->setClassName('OroCRM\Bundle\MagentoBundle\Entity\NewsletterSubscriber');

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

        /** @var TokenInterface $token */
        $token = $this->getMock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
        $this->assertEquals(
            $expected,
            $this->voter->vote($token, $object, $attributes)
        );
    }

    /**
     * @return array
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function attributesDataProvider()
    {
        $className = 'OroCRM\Bundle\MagentoBundle\Entity\NewsletterSubscriber';
        $objectIdentityClass = 'Symfony\Component\Security\Acl\Model\ObjectIdentityInterface';


        return [
            // has not applicable channels
            [
                $this->getObjectIdentity($objectIdentityClass, $className),
                $objectIdentityClass,
                ['VIEW'],
                false,
                false,
                NewsletterSubscriberVoter::ACCESS_ABSTAIN,
            ],
            [
                $this->getObjectIdentity($objectIdentityClass, $className),
                $objectIdentityClass,
                ['CREATE'],
                false,
                false,
                NewsletterSubscriberVoter::ACCESS_DENIED,
            ],
            [
                $this->getObjectIdentity($objectIdentityClass, $className),
                $objectIdentityClass,
                ['EDIT'],
                false,
                false,
                NewsletterSubscriberVoter::ACCESS_DENIED,
            ],
            [
                $this->getObjectIdentity($objectIdentityClass, $className),
                $objectIdentityClass,
                ['DELETE'],
                false,
                false,
                NewsletterSubscriberVoter::ACCESS_ABSTAIN
            ],
            [
                $this->getObjectIdentity($objectIdentityClass, $className),
                $objectIdentityClass,
                ['ASSIGN'],
                false,
                false,
                NewsletterSubscriberVoter::ACCESS_ABSTAIN
            ],
            // channel not applicable
            [$this->getSubscriber(), $className, ['VIEW'], true, false, NewsletterSubscriberVoter::ACCESS_ABSTAIN],
            [$this->getSubscriber(), $className, ['CREATE'], true, false, NewsletterSubscriberVoter::ACCESS_DENIED],
            [$this->getSubscriber(), $className, ['EDIT'], true, false, NewsletterSubscriberVoter::ACCESS_DENIED],
            [$this->getSubscriber(), $className, ['DELETE'], true, false, NewsletterSubscriberVoter::ACCESS_ABSTAIN],
            [$this->getSubscriber(), $className, ['ASSIGN'], true, false, NewsletterSubscriberVoter::ACCESS_ABSTAIN],
            // without customer
            [$this->getSubscriber(), $className, ['VIEW'], true, true, NewsletterSubscriberVoter::ACCESS_ABSTAIN],
            [$this->getSubscriber(), $className, ['CREATE'], true, true, NewsletterSubscriberVoter::ACCESS_ABSTAIN],
            [$this->getSubscriber(), $className, ['EDIT'], true, true, NewsletterSubscriberVoter::ACCESS_ABSTAIN],
            [$this->getSubscriber(), $className, ['DELETE'], true, true, NewsletterSubscriberVoter::ACCESS_ABSTAIN],
            [$this->getSubscriber(), $className, ['ASSIGN'], true, true, NewsletterSubscriberVoter::ACCESS_ABSTAIN],
            // with customer and without origin id
            [$this->getSubscriber(true), $className, ['VIEW'], true, true, NewsletterSubscriberVoter::ACCESS_ABSTAIN],
            [$this->getSubscriber(true), $className, ['CREATE'], true, true, NewsletterSubscriberVoter::ACCESS_DENIED],
            [$this->getSubscriber(true), $className, ['EDIT'], true, true, NewsletterSubscriberVoter::ACCESS_DENIED],
            [$this->getSubscriber(true), $className, ['DELETE'], true, true, NewsletterSubscriberVoter::ACCESS_ABSTAIN],
            [$this->getSubscriber(true), $className, ['ASSIGN'], true, true, NewsletterSubscriberVoter::ACCESS_ABSTAIN],
            // applicable with origin id
            [
                $this->getSubscriber(true, 1),
                $className,
                ['VIEW'],
                true,
                true,
                NewsletterSubscriberVoter::ACCESS_ABSTAIN
            ],
            [
                $this->getSubscriber(true, 1),
                $className,
                ['CREATE'],
                true,
                true,
                NewsletterSubscriberVoter::ACCESS_ABSTAIN
            ],
            [
                $this->getSubscriber(true, 1),
                $className,
                ['EDIT'],
                true,
                true,
                NewsletterSubscriberVoter::ACCESS_ABSTAIN
            ],
            [
                $this->getSubscriber(true, 1),
                $className,
                ['DELETE'],
                true,
                true,
                NewsletterSubscriberVoter::ACCESS_ABSTAIN
            ],
            [
                $this->getSubscriber(true, 1),
                $className,
                ['ASSIGN'],
                true,
                true,
                NewsletterSubscriberVoter::ACCESS_ABSTAIN
            ],
            // applicable but without channels
            [$this->getSubscriber(1), $className, ['VIEW'], false, true, NewsletterSubscriberVoter::ACCESS_ABSTAIN],
            [$this->getSubscriber(1), $className, ['CREATE'], false, true, NewsletterSubscriberVoter::ACCESS_DENIED],
            [$this->getSubscriber(1), $className, ['EDIT'], false, true, NewsletterSubscriberVoter::ACCESS_DENIED],
            [$this->getSubscriber(1), $className, ['DELETE'], false, true, NewsletterSubscriberVoter::ACCESS_ABSTAIN],
            [$this->getSubscriber(1), $className, ['ASSIGN'], false, true, NewsletterSubscriberVoter::ACCESS_ABSTAIN]
        ];
    }

    /**
     * @param string $objectIdentityClass
     * @param string $className
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|NewsletterSubscriber
     */
    public function getObjectIdentity($objectIdentityClass, $className)
    {
        $objectIdentity = $this->getMock($objectIdentityClass);
        $objectIdentity->expects($this->any())
            ->method('getType')
            ->will($this->returnValue($className));

        return $objectIdentity;
    }

    /**
     * @param bool $hasCustomer
     * @param int $customerOriginId
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|NewsletterSubscriber
     */
    protected function getSubscriber($hasCustomer = false, $customerOriginId = null)
    {
        $newsletterSubscriber = $this->getMock('OroCRM\Bundle\MagentoBundle\Entity\NewsletterSubscriber');

        $channel = $this->getMock('Oro\Bundle\IntegrationBundle\Entity\Channel');
        $newsletterSubscriber->expects($this->any())
            ->method('getChannel')
            ->will($this->returnValue($channel));

        if ($hasCustomer) {
            $customer = $this->getMock('OroCRM\Bundle\MagentoBundle\Entity\Customer');

            $newsletterSubscriber->expects($this->any())
                ->method('getCustomer')
                ->will($this->returnValue($customer));

            if ($customerOriginId) {
                $customer->expects($this->once())
                    ->method('getOriginId')
                    ->will($this->returnValue($customerOriginId));
            }
        }

        return $newsletterSubscriber;
    }
}
