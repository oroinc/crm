<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Acl\Voter;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\MagentoBundle\Acl\Voter\CustomerVoter;
use Oro\Bundle\MagentoBundle\Entity\Customer;
use Oro\Bundle\MagentoBundle\Model\ChannelSettingsProvider;
use Symfony\Component\Security\Acl\Model\ObjectIdentityInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class CustomerVoterTest extends \PHPUnit\Framework\TestCase
{
    /** @var \PHPUnit\Framework\MockObject\MockObject|DoctrineHelper */
    private $doctrineHelper;

    /** @var \PHPUnit\Framework\MockObject\MockObject|ChannelSettingsProvider */
    private $settingsProvider;

    /** @var CustomerVoter */
    private $voter;

    protected function setUp(): void
    {
        $this->doctrineHelper = $this->createMock(DoctrineHelper::class);
        $this->settingsProvider = $this->createMock(ChannelSettingsProvider::class);
        $this->voter = new CustomerVoter($this->doctrineHelper);
        $this->voter->setSettingsProvider($this->settingsProvider);
    }

    /**
     * @dataProvider attributesDataProvider
     */
    public function testVote($object, $attributes, $hasApplicableChannels, $isChannelApplicable, $expected)
    {
        $this->voter->setClassName(Customer::class);

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
        $oid = $this->getObjectIdentity(Customer::class);

        return [
            // has not applicable channels
            [$oid, ['VIEW'], false, false, CustomerVoter::ACCESS_ABSTAIN],
            [$oid, ['CREATE'], false, false, CustomerVoter::ACCESS_DENIED],
            [$oid, ['EDIT'], false, false, CustomerVoter::ACCESS_DENIED],
            [$oid, ['DELETE'], false, false, CustomerVoter::ACCESS_ABSTAIN],
            [$oid, ['ASSIGN'], false, false, CustomerVoter::ACCESS_ABSTAIN],
            // channel not applicable
            [$this->getCustomer(), ['VIEW'], true, false, CustomerVoter::ACCESS_ABSTAIN],
            [$this->getCustomer(), ['CREATE'], true, false, CustomerVoter::ACCESS_DENIED],
            [$this->getCustomer(), ['EDIT'], true, false, CustomerVoter::ACCESS_DENIED],
            [$this->getCustomer(), ['DELETE'], true, false, CustomerVoter::ACCESS_ABSTAIN],
            [$this->getCustomer(), ['ASSIGN'], true, false, CustomerVoter::ACCESS_ABSTAIN],
            // applicable but without origin id
            [$this->getCustomer(), ['VIEW'], true, true, CustomerVoter::ACCESS_ABSTAIN],
            [$this->getCustomer(), ['CREATE'], true, true, CustomerVoter::ACCESS_ABSTAIN],
            [$this->getCustomer(), ['EDIT'], true, true, CustomerVoter::ACCESS_DENIED],
            [$this->getCustomer(), ['DELETE'], true, true, CustomerVoter::ACCESS_ABSTAIN],
            [$this->getCustomer(), ['ASSIGN'], true, true, CustomerVoter::ACCESS_ABSTAIN],
            // applicable with origin id
            [$this->getCustomer(1), ['VIEW'], true, true, CustomerVoter::ACCESS_ABSTAIN],
            [$this->getCustomer(1), ['CREATE'], true, true, CustomerVoter::ACCESS_ABSTAIN],
            [$this->getCustomer(1), ['EDIT'], true, true, CustomerVoter::ACCESS_ABSTAIN],
            [$this->getCustomer(1), ['DELETE'], true, true, CustomerVoter::ACCESS_ABSTAIN],
            [$this->getCustomer(1), ['ASSIGN'], true, true, CustomerVoter::ACCESS_ABSTAIN],
            // applicable but without channels
            [$this->getCustomer(1), ['VIEW'], false, true, CustomerVoter::ACCESS_ABSTAIN],
            [$this->getCustomer(1), ['CREATE'], false, true, CustomerVoter::ACCESS_DENIED],
            [$this->getCustomer(1), ['EDIT'], false, true, CustomerVoter::ACCESS_DENIED],
            [$this->getCustomer(1), ['DELETE'], false, true, CustomerVoter::ACCESS_ABSTAIN],
            [$this->getCustomer(1), ['ASSIGN'], false, true, CustomerVoter::ACCESS_ABSTAIN]
        ];
    }

    /**
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
     * @param int $originId
     *
     * @return Customer
     */
    private function getCustomer($originId = null)
    {
        $customer = new Customer();
        $customer->setChannel(new Channel());
        if ($originId) {
            $customer->setOriginId($originId);
        }

        return $customer;
    }
}
