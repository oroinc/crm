<?php

namespace OroCRM\Bundle\MarketingListBundle\Tests\Unit\Acl\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use OroCRM\Bundle\MarketingListBundle\Acl\Voter\MarketingListSegmentVoter;

class MarketingListSegmentVoterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MarketingListSegmentVoter
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

        $this->voter = new MarketingListSegmentVoter($this->doctrineHelper);
    }

    protected function tearDown()
    {
        unset($this->voter);
        unset($this->doctrineHelper);
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
            'CREATE' => ['CREATE', false],
            'EDIT' => ['EDIT', true],
            'DELETE' => ['DELETE', true],
            'ASSIGN' => ['ASSIGN', false],
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
            'not supported class' => ['NotSupportedClass', 'stdClass', false],
        ];
    }

    /**
     * @dataProvider attributesDataProvider
     * @param array $attributes
     * @param $marketingList
     * @param $expected
     */
    public function testVote($attributes, $marketingList, $expected)
    {
        $object = $this->getMockBuilder('Oro\Bundle\SegmentBundle\Entity\Segment')
            ->disableOriginalConstructor()
            ->getMock();

        $this->doctrineHelper->expects($this->once())
            ->method('getEntityClass')
            ->with($object)
            ->will($this->returnValue('\stdClass'));

        $this->voter->setClassName('\stdClass');

        $this->doctrineHelper->expects($this->once())
            ->method('getSingleEntityIdentifier')
            ->with($object, false)
            ->will($this->returnValue(1));

        if ($this->voter->supportsAttribute($attributes[0])) {
            $this->assertMarketingListLoad($marketingList);
        }

        /** @var TokenInterface $token */
        $token = $this->getMock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
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
        $marketingList = $this->getMockBuilder('OroCRM\Bundle\MarketingListBundle\Entity\MarketingList')
            ->disableOriginalConstructor()
            ->getMock();

        return [
            [['VIEW'], null, MarketingListSegmentVoter::ACCESS_ABSTAIN],
            [['CREATE'], null, MarketingListSegmentVoter::ACCESS_ABSTAIN],
            [['EDIT'], null, MarketingListSegmentVoter::ACCESS_ABSTAIN],
            [['DELETE'], null, MarketingListSegmentVoter::ACCESS_ABSTAIN],
            [['ASSIGN'], null, MarketingListSegmentVoter::ACCESS_ABSTAIN],

            [['VIEW'], $marketingList, MarketingListSegmentVoter::ACCESS_ABSTAIN],
            [['CREATE'], $marketingList, MarketingListSegmentVoter::ACCESS_ABSTAIN],
            [['EDIT'], $marketingList, MarketingListSegmentVoter::ACCESS_DENIED],
            [['DELETE'], $marketingList, MarketingListSegmentVoter::ACCESS_DENIED],
            [['ASSIGN'], $marketingList, MarketingListSegmentVoter::ACCESS_ABSTAIN],
        ];
    }

    /**
     * @param $marketingList
     */
    protected function assertMarketingListLoad($marketingList)
    {
        $repository = $this->getMockBuilder('\Doctrine\Common\Persistence\ObjectRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $repository
            ->expects($this->once())
            ->method('findOneBy')
            ->will($this->returnValue($marketingList));

        $this->doctrineHelper
            ->expects($this->once())
            ->method('getEntityRepository')
            ->will($this->returnValue($repository));
    }
}
