<?php

namespace Oro\Bundle\MagentoBundle\Tests\Functional\Fixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\MagentoBundle\Entity\Cart;
use Oro\Bundle\MagentoBundle\Entity\CartStatus;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowStep;
use Oro\Bundle\WorkflowBundle\Model\Workflow;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class LoadCartData extends AbstractFixture implements DependentFixtureInterface, ContainerAwareInterface
{
    use ContainerAwareTrait;

    const TEST_WORKFLOW_NAME = 'b2c_flow_abandoned_shopping_cart';

    private static $carts = [
        [
            'name' => 'OpenCart1',
            'status' => 'open',
            'grandTotal' => 10,
            'channel' => LoadChannelsData::ENABLED_CART_CHANNEL,
            'workflowStep' => 'open',
            'createdAt' => '2017-05-05 12:00'
        ],
        [
            'name' => 'OpenCart2',
            'status' => 'open',
            'grandTotal' => 20,
            'channel' => LoadChannelsData::DISABLED_CART_CHANNEL,
            'workflowStep' => 'open',
            'createdAt' => '2017-05-05 12:00'
        ],
        [
            'name' => 'OpenCart3',
            'status' => 'expired',
            'grandTotal' => 30,
            'channel' => LoadChannelsData::ENABLED_CART_CHANNEL,
            'workflowStep' => 'open',
            'createdAt' => '2017-05-05 12:00'
        ],
        [
            'name' => 'OpenCart4',
            'status' => 'purchased',
            'grandTotal' => 40,
            'channel' => LoadChannelsData::ENABLED_CART_CHANNEL,
            'workflowStep' => 'open',
            'createdAt' => '2017-05-05 12:00'
        ],
        [
            'name' => 'ConvertedCart1',
            'status' => 'open',
            'grandTotal' => 10,
            'channel' => LoadChannelsData::DISABLED_CART_CHANNEL,
            'workflowStep' => 'converted',
            'createdAt' => '2017-05-05 12:00'
        ],
        [
            'name' => 'ConvertedCart2',
            'status' => 'purchased',
            'grandTotal' => 20,
            'channel' => LoadChannelsData::ENABLED_CART_CHANNEL,
            'workflowStep' => 'converted',
            'createdAt' => '2017-05-05 12:00'
        ],
        [
            'name' => 'ConvertedCart3',
            'status' => 'expired',
            'grandTotal' => 30,
            'channel' => LoadChannelsData::ENABLED_CART_CHANNEL,
            'workflowStep' => 'converted',
            'createdAt' => '2017-05-05 12:00'
        ],
        [
            'name' => 'ConvertedCart4',
            'status' => 'open',
            'grandTotal' => 40,
            'channel' => LoadChannelsData::ENABLED_CART_CHANNEL,
            'workflowStep' => 'converted',
            'createdAt' => '2017-05-16 12:00'
        ],
        [
            'name' => 'ConvertedCart5',
            'status' => 'open',
            'grandTotal' => 50,
            'channel' => LoadChannelsData::ENABLED_CART_CHANNEL,
            'workflowStep' => 'converted',
            'createdAt' => '2017-05-26 12:00'
        ],
    ];

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        foreach (static::$carts as $cartData) {
            $cart = new Cart;
            $cart->setStatus($this->getCartStatus($manager, $cartData['status']));
            $cart->setGrandTotal($cartData['grandTotal']);

            $cart->setItemsQty(1)
                ->setItemsCount(1)
                ->setBaseCurrencyCode('USD')
                ->setStoreCurrencyCode('USD')
                ->setQuoteCurrencyCode('USD')
                ->setStoreToBaseRate(1)
                ->setDataChannel($this->getReference($cartData['channel']))
                ->setIsGuest(0)
                ->setCreatedAt(new \DateTime($cartData['createdAt']))
                ->setUpdatedAt(new \DateTime('NOW'));

            $this->setReference($cartData['name'], $cart);
            $manager->persist($cart);
        }

        $manager->flush();

        $this->applyWorkflowStepsData($manager);
    }

    /**
     * @param ObjectManager $manager
     */
    private function applyWorkflowStepsData(ObjectManager $manager)
    {
        $workflowManager = $this->container->get('oro_workflow.manager');
        $workflow = $workflowManager->getWorkflow(self::TEST_WORKFLOW_NAME);

        foreach (static::$carts as $cartData) {
            $cart = $this->getReference($cartData['name']);

            $workflowManager
                ->getWorkflowItem($cart, self::TEST_WORKFLOW_NAME)
                ->setCurrentStep($this->getStepEntity($manager, $workflow, $cartData['workflowStep']));
        }

        $manager->flush();
    }

    /**
     * @param ObjectManager $manager
     * @param Workflow $workflow
     * @param $stepName
     * @return WorkflowStep
     */
    private function getStepEntity(ObjectManager $manager, Workflow $workflow, $stepName)
    {
        $stepRepository = $manager->getRepository(WorkflowStep::class);

        return $stepRepository->findOneBy([
            'name' => $stepName,
            'definition' => $workflow->getDefinition()
        ]);
    }

    /**
     * @param ObjectManager $manager
     * @param string $statusName
     * @return object|CartStatus
     */
    private function getCartStatus(ObjectManager $manager, $statusName)
    {
        return $manager->getRepository(CartStatus::class)->find($statusName);
    }

    /**
     * This method must return an array of fixtures classes
     * on which the implementing class depends on
     *
     * @return array
     */
    public function getDependencies()
    {
        return [
            LoadChannelsData::class
        ];
    }
}
