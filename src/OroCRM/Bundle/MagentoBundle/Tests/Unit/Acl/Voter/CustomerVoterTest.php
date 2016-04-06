<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\Acl\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

use OroCRM\Bundle\MagentoBundle\Acl\Voter\CustomerVoter;
use OroCRM\Bundle\MagentoBundle\Entity\Customer;

class CustomerVoterTest extends AbstractTwoWaySyncVoterTest
{
    /**
     * @var CustomerVoter
     */
    protected $voter;

    protected function setUp()
    {
        parent::setUp();

        $this->voter = new CustomerVoter($this->doctrineHelper);
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

        $this->voter->setClassName('OroCRM\Bundle\MagentoBundle\Entity\Customer');

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
        $className = 'OroCRM\Bundle\MagentoBundle\Entity\Customer';
        $objectIdentityClass = 'Symfony\Component\Security\Acl\Model\ObjectIdentityInterface';


        return [
            // has not applicable channels
            [
                $this->getObjectIdentity($objectIdentityClass, $className),
                $objectIdentityClass, ['VIEW'],
                false,
                false,
                CustomerVoter::ACCESS_ABSTAIN
            ],
            [
                $this->getObjectIdentity($objectIdentityClass, $className),
                $objectIdentityClass,
                ['CREATE'],
                false,
                false,
                CustomerVoter::ACCESS_DENIED
            ],
            [
                $this->getObjectIdentity($objectIdentityClass, $className),
                $objectIdentityClass,
                ['EDIT'],
                false,
                false,
                CustomerVoter::ACCESS_DENIED
            ],
            [
                $this->getObjectIdentity($objectIdentityClass, $className),
                $objectIdentityClass,
                ['DELETE'],
                false,
                false,
                CustomerVoter::ACCESS_ABSTAIN
            ],
            [
                $this->getObjectIdentity($objectIdentityClass, $className),
                $objectIdentityClass,
                ['ASSIGN'],
                false,
                false,
                CustomerVoter::ACCESS_ABSTAIN
            ],
            // channel not applicable
            [$this->getCustomer(), $className, ['VIEW'], true, false, CustomerVoter::ACCESS_ABSTAIN],
            [$this->getCustomer(), $className, ['CREATE'], true, false, CustomerVoter::ACCESS_DENIED],
            [$this->getCustomer(), $className, ['EDIT'], true, false, CustomerVoter::ACCESS_DENIED],
            [$this->getCustomer(), $className, ['DELETE'], true, false, CustomerVoter::ACCESS_ABSTAIN],
            [$this->getCustomer(), $className, ['ASSIGN'], true, false, CustomerVoter::ACCESS_ABSTAIN],
            // applicable but without origin id
            [$this->getCustomer(), $className, ['VIEW'], true, true, CustomerVoter::ACCESS_ABSTAIN],
            [$this->getCustomer(), $className, ['CREATE'], true, true, CustomerVoter::ACCESS_ABSTAIN],
            [$this->getCustomer(), $className, ['EDIT'], true, true, CustomerVoter::ACCESS_DENIED],
            [$this->getCustomer(), $className, ['DELETE'], true, true, CustomerVoter::ACCESS_ABSTAIN],
            [$this->getCustomer(), $className, ['ASSIGN'], true, true, CustomerVoter::ACCESS_ABSTAIN],
            // applicable with origin id
            [$this->getCustomer(1), $className, ['VIEW'], true, true, CustomerVoter::ACCESS_ABSTAIN],
            [$this->getCustomer(1), $className, ['CREATE'], true, true, CustomerVoter::ACCESS_ABSTAIN],
            [$this->getCustomer(1), $className, ['EDIT'], true, true, CustomerVoter::ACCESS_ABSTAIN],
            [$this->getCustomer(1), $className, ['DELETE'], true, true, CustomerVoter::ACCESS_ABSTAIN],
            [$this->getCustomer(1), $className, ['ASSIGN'], true, true, CustomerVoter::ACCESS_ABSTAIN],
            // applicable but without channels
            [$this->getCustomer(1), $className, ['VIEW'], false, true, CustomerVoter::ACCESS_ABSTAIN],
            [$this->getCustomer(1), $className, ['CREATE'], false, true, CustomerVoter::ACCESS_DENIED],
            [$this->getCustomer(1), $className, ['EDIT'], false, true, CustomerVoter::ACCESS_DENIED],
            [$this->getCustomer(1), $className, ['DELETE'], false, true, CustomerVoter::ACCESS_ABSTAIN],
            [$this->getCustomer(1), $className, ['ASSIGN'], false, true, CustomerVoter::ACCESS_ABSTAIN]
        ];
    }

    /**
     * @param string $objectIdentityClass
     * @param string $className
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|Customer
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
     * @param int $originId
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|Customer
     */
    protected function getCustomer($originId = null)
    {
        $customer = $this->getMock('OroCRM\Bundle\MagentoBundle\Entity\Customer');
        $channel = $this->getMock('Oro\Bundle\IntegrationBundle\Entity\Channel');
        $customer->expects($this->any())
            ->method('getChannel')
            ->will($this->returnValue($channel));

        if ($originId) {
            $customer->expects($this->any())
                ->method('getOriginId')
                ->will($this->returnValue($originId));
        }

        return $customer;
    }
}
