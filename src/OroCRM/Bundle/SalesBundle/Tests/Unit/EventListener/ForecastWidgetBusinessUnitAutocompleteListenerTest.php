<?php

namespace OroCRM\Bundle\SalesBundle\Tests\Unit\EventListener;

use OroCRM\Bundle\SalesBundle\EventListener\ForecastWidgetBusinessUnitAutocompleteListener;
use Oro\Bundle\SearchBundle\Query\Criteria\Criteria;

class ForecastWidgetBusinessUnitAutocompleteListenerTest extends \PHPUnit_Framework_TestCase
{
    const OPPORTUNITY_ENTITY = 'OpportunityEntity';

    /** @var ForecastWidgetBusinessUnitAutocompleteListener */
    protected $listener;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $businessUnitAclProvider;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $securityFacade;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $beforeSearchEvent;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $query;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $criteria;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $expr;

    protected function setUp()
    {
        $this->businessUnitAclProvider = $this
            ->getMockBuilder('Oro\Bundle\OrganizationBundle\Provider\BusinessUnitAclProvider')
            ->disableOriginalConstructor()
            ->getMock();

        $this->securityFacade = $this->getMockBuilder('Oro\Bundle\SecurityBundle\SecurityFacade')
            ->disableOriginalConstructor()
            ->getMock();

        $this->beforeSearchEvent = $this->getMockBuilder('Oro\Bundle\SearchBundle\Event\BeforeSearchEvent')
            ->setMethods(['getQuery'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->query = $this->getMockBuilder('Oro\Bundle\SearchBundle\Query\Query')
            ->setMethods(['getFrom', 'getCriteria'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->criteria = new Criteria();

        $this->expr = $this->getMockBuilder('Oro\Bundle\SearchBundle\Query\Criteria\ExpressionBuilder')
            ->setMethods(['eq', 'in'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->listener = new ForecastWidgetBusinessUnitAutocompleteListener(
            $this->securityFacade,
            $this->businessUnitAclProvider,
            self::OPPORTUNITY_ENTITY
        );
    }

    public function testSearch()
    {
        $this->beforeSearchEvent->expects($this->once())
            ->method('getQuery')
            ->will($this->returnValue($this->query));

        $this->query->expects($this->once())
            ->method('getFrom')
            ->will($this->returnValue(['oro_business_unit']));

        $this->query->expects($this->once())
            ->method('getCriteria')
            ->will($this->returnValue($this->criteria));

        $this->businessUnitAclProvider->expects($this->once())
            ->method('addOneShotIsGrantedObserver')
            ->will($this->returnValue($this->businessUnitAclProvider));

        $this->businessUnitAclProvider->expects($this->once())
            ->method('getBusinessUnitIds')
            ->with(self::OPPORTUNITY_ENTITY, 'VIEW')
            ->will($this->returnValue([1, 2, 3]));

        $this->listener->onSearchBefore($this->beforeSearchEvent);
    }
}
