<?php

namespace OroCRM\Bundle\SalesBundle\Tests\Unit\Provider;

use Oro\Bundle\DashboardBundle\Helper\DateHelper;
use Oro\Bundle\DashboardBundle\Model\WidgetOptionBag;
use Oro\Bundle\OrganizationBundle\Entity\BusinessUnit;
use OroCRM\Bundle\SalesBundle\Provider\ForecastOfOpportunities;
use Oro\Bundle\UserBundle\Entity\User;

class ForecastOfOpportunitiesTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockBuilder
     */
    protected $doctrine;

    /**
     * @var \PHPUnit_Framework_MockObject_MockBuilder
     */
    protected $translator;

    /**
     * @var \PHPUnit_Framework_MockObject_MockBuilder
     */
    protected $numberFormatter;

    /**
     * @var \PHPUnit_Framework_MockObject_MockBuilder
     */
    protected $dateTimeFormatter;

    /**
     * @var \PHPUnit_Framework_MockObject_MockBuilder
     */
    protected $aclHelper;

    /**
     * @var ForecastOfOpportunities
     */
    protected $provider;

    /**
     * @var \PHPUnit_Framework_MockObject_MockBuilder
     */
    protected $opportunityRepository;

    /**
     * @var \PHPUnit_Framework_MockObject_MockBuilder
     */
    protected $businessUnitRepository;

    /**
     * @var \PHPUnit_Framework_MockObject_MockBuilder
     */
    protected $userRepository;

    /** @var DateHelper|\PHPUnit_Framework_MockObject_MockBuilder */
    protected $dateHelper;

    /**
     * @var \PHPUnit_Framework_MockObject_MockBuilder
     */
    protected $ownerHelper;

    protected function setUp()
    {
        $opportunityRepository       = 'OroCRM\Bundle\SalesBundle\Entity\Repository\OpportunityRepository';
        $this->opportunityRepository = $this->getMockBuilder($opportunityRepository)
            ->disableOriginalConstructor()
            ->getMock();

        $businessUnitRepository       = 'Oro\Bundle\OrganizationBundle\Entity\Repository\BusinessUnitRepository';
        $this->businessUnitRepository = $this->getMockBuilder($businessUnitRepository)
            ->setMethods(['findById'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->userRepository = $this->getMockBuilder('Oro\Bundle\UserBundle\Entity\Repository\UserRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $this->doctrine = $this->getMockBuilder('Doctrine\Bundle\DoctrineBundle\Registry')
            ->disableOriginalConstructor()
            ->getMock();

        $this->doctrine->expects($this->any())
            ->method('getRepository')
            ->will($this->returnValueMap([
                ['OroCRMSalesBundle:Opportunity', null, $this->opportunityRepository],
                ['OroOrganizationBundle:BusinessUnit', null, $this->businessUnitRepository],
                ['OroUserBundle:User', null, $this->userRepository],
            ]));

        $this->translator = $this->getMockBuilder('Oro\Bundle\TranslationBundle\Translation\Translator')
            ->disableOriginalConstructor()
            ->getMock();

        $this->numberFormatter = $this->getMockBuilder('Oro\Bundle\LocaleBundle\Formatter\NumberFormatter')
            ->disableOriginalConstructor()
            ->getMock();

        $this->numberFormatter
            ->expects($this->any())
            ->method($this->anything())
            ->withAnyParameters()
            ->will($this->returnArgument(0));

        $this->dateTimeFormatter = $this->getMockBuilder('Oro\Bundle\LocaleBundle\Formatter\DateTimeFormatter')
            ->disableOriginalConstructor()
            ->getMock();

        $this->dateTimeFormatter
            ->expects($this->any())
            ->method($this->anything())
            ->withAnyParameters()
            ->will($this->returnArgument(0));

        $this->aclHelper = $this->getMockBuilder('Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper')
            ->disableOriginalConstructor()
            ->getMock();

        $this->dateHelper = $this->getMockBuilder('Oro\Bundle\DashboardBundle\Helper\DateHelper')
            ->disableOriginalConstructor()
            ->getMock();

        $this->ownerHelper = $this->getMockBuilder('\Oro\Bundle\UserBundle\Dashboard\OwnerHelper')
            ->disableOriginalConstructor()
            ->getMock();
        
        $this->provider = new ForecastOfOpportunities(
            $this->doctrine,
            $this->numberFormatter,
            $this->dateTimeFormatter,
            $this->aclHelper,
            $this->translator,
            $this->dateHelper,
            $this->ownerHelper
        );
    }

    public function tearDown()
    {
        unset(
            $this->doctrine,
            $this->numberFormatter,
            $this->dateTimeFormatter,
            $this->aclHelper,
            $this->translator,
            $this->securityFacade,
            $this->dateHelper,
            $this->ownerHelper
        );
    }

    public function testForecastOfOpportunitiesValuesWithUserAutoFill()
    {
        $user = new User();
        $user->setId(1);
        $options       = ['owners' => [], 'businessUnits' => [], 'roles' => [], 'dateRange' => ['start' => null, 'end' => null]];
        $widgetOptions = new WidgetOptionBag($options);
        $this->ownerHelper->expects($this->any())
            ->method('getOwnerIds')
            ->willReturn([]);

        $this->opportunityRepository->expects($this->any())
            ->method('getForecastOfOpportunitiesData')
            ->with([], null, $this->aclHelper)
            ->will($this->returnValue(['inProgressCount' => 5, 'budgetAmount' => 1000, 'weightedForecast' => 500]));

        $result = $this->provider
            ->getForecastOfOpportunitiesValues($widgetOptions, 'getInProgressValues', 'integer', false);
        $this->assertEquals(['value' => 5], $result);

        $result = $this->provider
            ->getForecastOfOpportunitiesValues($widgetOptions, 'getTotalForecastValues', 'currency', false);
        $this->assertEquals(['value' => 1000], $result);

        $result = $this->provider
            ->getForecastOfOpportunitiesValues($widgetOptions, 'getWeightedForecastValues', 'currency', false);
        $this->assertEquals(['value' => 500], $result);
    }

    public function testForecastOfOpportunitiesValues()
    {
        $user = new User();
        $user->setId(1);
        $options       = ['owners' => [$user], 'businessUnits' => [], 'roles' => [], 'dateRange' => ['start' => null, 'end' => null]];
        $widgetOptions = new WidgetOptionBag($options);
        $this->ownerHelper->expects($this->any())
            ->method('getOwnerIds')
            ->willReturn([$user->getId()]);

        $this->opportunityRepository->expects($this->any())
            ->method('getForecastOfOpportunitiesData')
            ->with([$user->getId()], null, $this->aclHelper)
            ->will($this->returnValue(['inProgressCount' => 5, 'budgetAmount' => 1000, 'weightedForecast' => 500]));

        $result = $this->provider
            ->getForecastOfOpportunitiesValues($widgetOptions, 'getInProgressValues', 'integer', false);
        $this->assertEquals(['value' => 5], $result);

        $result = $this->provider
            ->getForecastOfOpportunitiesValues($widgetOptions, 'getTotalForecastValues', 'currency', false);
        $this->assertEquals(['value' => 1000], $result);

        $result = $this->provider
            ->getForecastOfOpportunitiesValues($widgetOptions, 'getWeightedForecastValues', 'currency', false);
        $this->assertEquals(['value' => 500], $result);
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function testForecastOfOpportunitiesValuesWithCompareDate()
    {
        $this->markTestSkipped('Skipped until CRM-5569 and CRM-5565 will be done');
        $user = new User();
        $user->setId(1);

        $date = '2015-09-20 00:00:00.000000';

        $options = [
            'owners' => ['users' => [$user->getId()]],
        $options       = [
            'owners'        => [$user],
            'businessUnits' => [],
            'compareToDate' => ['useDate' => true, 'date' => $date],
            'dateRange'     => ['start' => null, 'end' => null]
        ];
        $widgetOptions = new WidgetOptionBag($options);
        $this->ownerHelper->expects($this->any())
            ->method('getOwnerIds')
            ->willReturn([$user->getId()]);

        $resultValues = function ($users, $date, $aclHelper) {
            if ($date === null) {
                return ['inProgressCount' => 5, 'budgetAmount' => 1000, 'weightedForecast' => 500];
            }

            return ['inProgressCount' => 2, 'budgetAmount' => 200, 'weightedForecast' => 50];
        };

        $this->opportunityRepository->expects($this->any())
            ->method('getForecastOfOpportunitiesData')
            ->with($this->logicalOr([$user->getId()], $this->logicalOr($date, null), $this->aclHelper))
            ->will($this->returnCallback($resultValues));

        $result = $this->provider
            ->getForecastOfOpportunitiesValues($widgetOptions, 'getInProgressValues', 'integer', false);

        $expectedResult = ['value' => 5, 'deviation' => '+3 (+1.5)', 'isPositive' => true, 'previousRange' => $date];
        $this->assertEquals($expectedResult, $result);

        $expectedResult = ['value' => 1000, 'deviation' => '+800 (+4)', 'isPositive' => 1, 'previousRange' => $date];
        $result         = $this->provider
            ->getForecastOfOpportunitiesValues($widgetOptions, 'getTotalForecastValues', 'currency', false);
        $this->assertEquals($expectedResult, $result);

        $expectedResult = ['value' => 500, 'deviation' => '+450 (+9)', 'isPositive' => 1, 'previousRange' => $date];
        $result         = $this->provider
            ->getForecastOfOpportunitiesValues($widgetOptions, 'getWeightedForecastValues', 'currency', false);
        $this->assertEquals($expectedResult, $result);
    }

    public function testForecastOfOpportunitiesValuesWithBusinessUnits()
    {
        $user = new User();
        $user->setId(1);

        $businessUnit = new BusinessUnit();
        $businessUnit->addUser($user);

        $options       = ['owners' => [], 'businessUnits' => [$businessUnit->getId()], 'dateRange' => ['start' => null, 'end' => null]];
        $widgetOptions = new WidgetOptionBag($options);
        $this->ownerHelper->expects($this->any())
            ->method('getOwnerIds')
            ->willReturn([$user->getId()]);

        $this->opportunityRepository->expects($this->any())
            ->method('getForecastOfOpportunitiesData')
            ->with([$user->getId()], null, $this->aclHelper)
            ->will($this->returnValue(['inProgressCount' => 5, 'budgetAmount' => 1000, 'weightedForecast' => 500]));

        $this->businessUnitRepository->expects($this->any())
            ->method('findById')
            ->will($this->returnValue([$businessUnit]));

        $result = $this->provider
            ->getForecastOfOpportunitiesValues($widgetOptions, 'getInProgressValues', 'integer', false);
        $this->assertEquals(['value' => 5], $result);

        $result = $this->provider
            ->getForecastOfOpportunitiesValues($widgetOptions, 'getTotalForecastValues', 'currency', false);
        $this->assertEquals(['value' => 1000], $result);

        $result = $this->provider
            ->getForecastOfOpportunitiesValues($widgetOptions, 'getWeightedForecastValues', 'currency', false);
        $this->assertEquals(['value' => 500], $result);
    }
}
