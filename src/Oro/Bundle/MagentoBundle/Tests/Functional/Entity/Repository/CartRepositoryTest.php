<?php

namespace Oro\Bundle\MagentoBundle\Tests\Functional\Entity\Repository;

use Oro\Bundle\MagentoBundle\Entity\Cart;
use Oro\Bundle\MagentoBundle\Entity\Repository\CartRepository;
use Oro\Bundle\MagentoBundle\Tests\Functional\Fixtures\LoadCartData;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class CartRepositoryTest extends WebTestCase
{
    /**
     * @var AclHelper
     */
    private $aclHelper;

    /**
     * @var CartRepository
     */
    private $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->initClient();
        $this->loadFixtures([LoadCartData::class]);

        $this->aclHelper = static::getContainer()->get('oro_security.acl_helper');

        $this->repository = static::getContainer()->get('doctrine')
            ->getManagerForClass(Cart::class)
            ->getRepository(Cart::class);
    }

    public function testGetFunnelChartDataWithWorkflow()
    {
        $workflowManager = static::getContainer()->get('oro_workflow.manager');
        $workflow = $workflowManager->getWorkflow(LoadCartData::TEST_WORKFLOW_NAME);

        $expectedData = [
            [
                'label' => 'oro.workflow.b2c_flow_abandoned_shopping_cart.step.open.label',
                 //value=OpenCart1 [not applied: OpenCart2(disabled channel), OpenCart3(expired), OpenCart4(purchased)]
                'value' => 10,
                'isNozzle' => false
            ],
            [
                'label' => 'oro.workflow.b2c_flow_abandoned_shopping_cart.step.converted.label',
                //value=ConvertedCart4+ConvertedCart5 [not applied: ConvertedCart1(disabled channel),
                //ConvertedCart2(purchased), ConvertedCart3(expired)]
                'value' => 90,
                'isNozzle' => true
            ]
        ];

        $this->assertEquals(
            $expectedData,
            $this->repository->getFunnelChartData(null, null, $workflow, $this->aclHelper)
        );
    }

    public function testGetFunnelChartDataWithoutWorkflow()
    {
        $expectedData = ['items' => [], 'nozzleSteps' => []];

        $this->assertEquals(
            $expectedData,
            $this->repository->getFunnelChartData(null, null, null, $this->aclHelper)
        );
    }

    public function testGetFunnelChartDataWithWorkflowAndDates()
    {
        $workflowManager = static::getContainer()->get('oro_workflow.manager');
        $workflow = $workflowManager->getWorkflow(LoadCartData::TEST_WORKFLOW_NAME);

        $expectedData = [
            [
                'label' => 'oro.workflow.b2c_flow_abandoned_shopping_cart.step.open.label',
                'value' => 10, // = OpenCart1 (date is not affected for not final steps)
                'isNozzle' => false
            ],
            [
                'label' => 'oro.workflow.b2c_flow_abandoned_shopping_cart.step.converted.label',
                'value' => 40, // = ConvertedCart4 (ConvertedCart5 doesn't apply because of date range)
                'isNozzle' => true
            ]
        ];

        $this->assertEquals(
            $expectedData,
            $this->repository->getFunnelChartData(
                new \DateTime('2017-05-10'),
                new \DateTime('2017-05-20'),
                $workflow,
                $this->aclHelper
            )
        );
    }
}
