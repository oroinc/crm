<?php

namespace OroCRM\Bundle\MarketingListBundle\Tests\Unit\Acl\Voter;

use OroCRM\Bundle\MarketingListBundle\Acl\Voter\MarketingListSegmentVoter;

class MarketingListSegmentVoterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MarketingListSegmentVoter
     */
    protected $voter;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $registry;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $doctrineHelper;

    protected function setUp()
    {
        $this->registry = $this->getMockBuilder('Doctrine\Common\Persistence\ManagerRegistry')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->doctrineHelper = $this->getMockBuilder('Oro\Bundle\EntityBundle\ORM\DoctrineHelper')
            ->disableOriginalConstructor()
            ->getMock();

        $this->voter = new MarketingListSegmentVoter($this->registry, $this->doctrineHelper);
    }

    protected function tearDown()
    {
        unset($this->voter);
        unset($this->registry);
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

    public function supportsAttributeDataProvider()
    {
        return array(
            'VIEW' => array('VIEW', false),
            'CREATE' => array('CREATE', false),
            'EDIT' => array('EDIT', true),
            'DELETE' => array('DELETE', true),
            'ASSIGN' => array('ASSIGN', false),
        );
    }

    /**
     * @param string $class
     * @param bool $expected
     * @dataProvider supportsClassDataProvider
     */
    public function testSupportsClass($class, $expected)
    {
        $this->assertEquals($expected, $this->voter->supportsClass($class));
    }

    public function supportsClassDataProvider()
    {
        return array(
            'supported class' => array(MarketingListSegmentVoter::SEGMENT_ENTITY, true),
            'not supported class' => array('NotSupportedClass', false),
        );
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
            ->will($this->returnValue(MarketingListSegmentVoter::SEGMENT_ENTITY));
        $this->doctrineHelper->expects($this->once())
            ->method('getSingleEntityIdentifier')
            ->with($object, false)
            ->will($this->returnValue(1));

        if ($this->voter->supportsAttribute($attributes[0])) {
            $this->assertMarketingListLoad($marketingList);
        }

        $token = $this->getMock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
        $this->assertEquals(
            $expected,
            $this->voter->vote($token, $object, $attributes)
        );
    }

    public function attributesDataProvider()
    {
        $marketingList = $this->getMockBuilder('OroCRM\Bundle\MarketingListBundle\Entity\MarketingList')
            ->disableOriginalConstructor()
            ->getMock();

        return array(
            array(array('VIEW'), null, MarketingListSegmentVoter::ACCESS_ABSTAIN),
            array(array('CREATE'), null, MarketingListSegmentVoter::ACCESS_ABSTAIN),
            array(array('EDIT'), null, MarketingListSegmentVoter::ACCESS_ABSTAIN),
            array(array('DELETE'), null, MarketingListSegmentVoter::ACCESS_ABSTAIN),
            array(array('ASSIGN'), null, MarketingListSegmentVoter::ACCESS_ABSTAIN),

            array(array('VIEW'), $marketingList, MarketingListSegmentVoter::ACCESS_ABSTAIN),
            array(array('CREATE'), $marketingList, MarketingListSegmentVoter::ACCESS_ABSTAIN),
            array(array('EDIT'), $marketingList, MarketingListSegmentVoter::ACCESS_DENIED),
            array(array('DELETE'), $marketingList, MarketingListSegmentVoter::ACCESS_DENIED),
            array(array('ASSIGN'), $marketingList, MarketingListSegmentVoter::ACCESS_ABSTAIN),
        );
    }

    protected function assertMarketingListLoad($marketingList)
    {
        $repository = $this->getMockBuilder('\Doctrine\Common\Persistence\ObjectRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $repository->expects($this->once())
            ->method('findOneBy')
            ->will($this->returnValue($marketingList));
        $em = $this->getMockBuilder('\Doctrine\Common\Persistence\ObjectManager')
            ->disableOriginalConstructor()
            ->getMock();
        $em->expects($this->once())
            ->method('getRepository')
            ->with('OroCRMMarketingListBundle:MarketingList')
            ->will($this->returnValue($repository));
        $this->registry->expects($this->once())
            ->method('getManager')
            ->will($this->returnValue($em));
    }
}
