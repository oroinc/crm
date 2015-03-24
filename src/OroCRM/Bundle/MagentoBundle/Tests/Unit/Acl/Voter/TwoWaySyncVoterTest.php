<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\Acl\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

use Oro\Bundle\DataGridBundle\Common\Object;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\IntegrationBundle\Entity\Channel;

use OroCRM\Bundle\MagentoBundle\Acl\Voter\TwoWaySyncVoter;
use OroCRM\Bundle\MagentoBundle\Entity\Customer;

class TwoWaySyncVoterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TwoWaySyncVoter
     */
    protected $voter;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|DoctrineHelper
     */
    protected $doctrineHelper;

    protected function setUp()
    {
        $this->doctrineHelper = $this->getMockBuilder('Oro\Bundle\EntityBundle\ORM\DoctrineHelper')
            ->disableOriginalConstructor()
            ->getMock();

        $this->voter = new TwoWaySyncVoter($this->doctrineHelper);
        $this->voter->setChannelClassName('\stdClass');
    }

    protected function tearDown()
    {
        unset($this->voter, $this->doctrineHelper);
    }

    /**
     * @param string $attribute
     * @param bool $expected
     * @dataProvider supportsAttributeDataProvider
     */
    public function testSupportsAttribute($attribute, $expected)
    {
        $this->assertEquals($expected, $this->voter->supportsAttribute($attribute));
    }

    /**
     * @return array
     */
    public function supportsAttributeDataProvider()
    {
        return [
            'VIEW' => ['VIEW', false],
            'CREATE' => ['CREATE', true],
            'EDIT' => ['EDIT', true],
            'DELETE' => ['DELETE', false],
            'ASSIGN' => ['ASSIGN', false]
        ];
    }

    /**
     * @param string $class
     * @param string $actualClass
     * @param bool $expected
     * @dataProvider supportsClassDataProvider
     */
    public function testSupportsClass($class, $actualClass, $expected)
    {
        $this->voter->setClassName($actualClass);

        $this->assertEquals($expected, $this->voter->supportsClass($class));
    }

    /**
     * @return array
     */
    public function supportsClassDataProvider()
    {
        return [
            'supported class' => ['stdClass', 'stdClass', true],
            'not supported class' => ['NotSupportedClass', 'stdClass', false]
        ];
    }

    /**
     * @param object $object
     * @param string $className
     * @param array $attributes
     * @param array $channels
     * @param bool $expected
     *
     * @dataProvider attributesDataProvider
     */
    public function testVote($object, $className, $attributes, $channels, $expected)
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

        $repository = $this->getMockBuilder('\Doctrine\Common\Persistence\ObjectRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $repository
            ->expects($this->any())
            ->method('findBy')
            ->with($this->isType('array'))
            ->will($this->returnValue($channels));

        $this->doctrineHelper
            ->expects($this->any())
            ->method('getEntityRepository')
            ->will($this->returnValue($repository));

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
        $objectIdentity = $this->getMock($objectIdentityClass);
        $objectIdentity->expects($this->any())
            ->method('getType')
            ->will($this->returnValue($className));

        return [
            [$this->getCustomer(), $className, ['VIEW'], [], TwoWaySyncVoter::ACCESS_ABSTAIN],
            [$this->getCustomer(), $className, ['CREATE'], [], TwoWaySyncVoter::ACCESS_DENIED],
            [$this->getCustomer(), $className, ['EDIT'], [], TwoWaySyncVoter::ACCESS_DENIED],
            [$this->getCustomer(), $className, ['DELETE'], [], TwoWaySyncVoter::ACCESS_ABSTAIN],
            [$this->getCustomer(), $className, ['ASSIGN'], [], TwoWaySyncVoter::ACCESS_ABSTAIN],
            [$this->getCustomer(true), $className, ['VIEW'], [], TwoWaySyncVoter::ACCESS_ABSTAIN],
            [$this->getCustomer(true), $className, ['CREATE'], [], TwoWaySyncVoter::ACCESS_ABSTAIN],
            [$this->getCustomer(true), $className, ['EDIT'], [], TwoWaySyncVoter::ACCESS_ABSTAIN],
            [$this->getCustomer(true), $className, ['DELETE'], [], TwoWaySyncVoter::ACCESS_ABSTAIN],
            [$this->getCustomer(true), $className, ['ASSIGN'], [], TwoWaySyncVoter::ACCESS_ABSTAIN],
            [$objectIdentity, $objectIdentityClass, ['VIEW'], [], TwoWaySyncVoter::ACCESS_ABSTAIN],
            [$objectIdentity, $objectIdentityClass, ['CREATE'], [], TwoWaySyncVoter::ACCESS_DENIED],
            [$objectIdentity, $objectIdentityClass, ['EDIT'], [], TwoWaySyncVoter::ACCESS_DENIED],
            [$objectIdentity, $objectIdentityClass, ['DELETE'], [], TwoWaySyncVoter::ACCESS_ABSTAIN],
            [$objectIdentity, $objectIdentityClass, ['ASSIGN'], [], TwoWaySyncVoter::ACCESS_ABSTAIN],
            [
                $objectIdentity,
                $objectIdentityClass,
                ['VIEW'],
                [$this->getChannel(), $this->getChannel(true)],
                TwoWaySyncVoter::ACCESS_ABSTAIN
            ],
            [
                $objectIdentity,
                $objectIdentityClass,
                ['CREATE'],
                [$this->getChannel(), $this->getChannel(true)],
                TwoWaySyncVoter::ACCESS_ABSTAIN
            ],
            [
                $objectIdentity,
                $objectIdentityClass,
                ['EDIT'],
                [$this->getChannel(), $this->getChannel(true)],
                TwoWaySyncVoter::ACCESS_ABSTAIN
            ],
            [
                $objectIdentity,
                $objectIdentityClass,
                ['DELETE'],
                [$this->getChannel(), $this->getChannel(true)],
                TwoWaySyncVoter::ACCESS_ABSTAIN
            ],
            [
                $objectIdentity,
                $objectIdentityClass,
                ['ASSIGN'],
                [$this->getChannel(), $this->getChannel(true)],
                TwoWaySyncVoter::ACCESS_ABSTAIN
            ],
            [
                $objectIdentity,
                $objectIdentityClass,
                ['VIEW'],
                [$this->getChannel(), $this->getChannel()],
                TwoWaySyncVoter::ACCESS_ABSTAIN
            ],
            [
                $objectIdentity,
                $objectIdentityClass,
                ['CREATE'],
                [$this->getChannel(), $this->getChannel()],
                TwoWaySyncVoter::ACCESS_DENIED
            ],
            [
                $objectIdentity,
                $objectIdentityClass,
                ['EDIT'],
                [$this->getChannel(), $this->getChannel()],
                TwoWaySyncVoter::ACCESS_DENIED
            ],
            [
                $objectIdentity,
                $objectIdentityClass,
                ['DELETE'],
                [$this->getChannel(), $this->getChannel()],
                TwoWaySyncVoter::ACCESS_ABSTAIN
            ],
            [
                $objectIdentity,
                $objectIdentityClass,
                ['ASSIGN'],
                [$this->getChannel(), $this->getChannel()],
                TwoWaySyncVoter::ACCESS_ABSTAIN
            ],
            [
                $objectIdentity,
                $objectIdentityClass,
                ['CREATE'],
                [$this->getChannel(true), $this->getChannel(true)],
                TwoWaySyncVoter::ACCESS_ABSTAIN
            ],
            [
                $objectIdentity,
                $objectIdentityClass,
                ['EDIT'],
                [$this->getChannel(true), $this->getChannel(true)],
                TwoWaySyncVoter::ACCESS_ABSTAIN
            ],
            [
                $objectIdentity,
                $objectIdentityClass,
                ['EDIT'],
                [$this->getChannel(null)],
                TwoWaySyncVoter::ACCESS_DENIED
            ]
        ];
    }

    /**
     * @param bool $isTwoWaySyncEnabled
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|Customer
     */
    protected function getCustomer($isTwoWaySyncEnabled = false)
    {
        $customer = $this->getMockBuilder('OroCRM\Bundle\MagentoBundle\Entity\Customer')
            ->disableOriginalConstructor()
            ->getMock();

        $channel = $this->getMock('Oro\Bundle\IntegrationBundle\Entity\Channel');
        $settings = Object::create(['isTwoWaySyncEnabled' => $isTwoWaySyncEnabled]);
        $channel->expects($this->any())
            ->method('getSynchronizationSettings')
            ->will($this->returnValue($settings));

        $customer->expects($this->once())
            ->method('getChannel')
            ->will($this->returnValue($channel));

        return $customer;
    }

    /**
     * @param bool $isTwoWaySyncEnabled
     *
     * @return Channel|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getChannel($isTwoWaySyncEnabled = false)
    {
        $channel = $this->getMock('Oro\Bundle\IntegrationBundle\Entity\Channel');

        $settings = [];
        if (null !== $isTwoWaySyncEnabled) {
            $settings['isTwoWaySyncEnabled'] = $isTwoWaySyncEnabled;
        }

        $settings = Object::create($settings);
        $channel->expects($this->any())
            ->method('getSynchronizationSettings')
            ->will($this->returnValue($settings));

        return $channel;
    }
}
