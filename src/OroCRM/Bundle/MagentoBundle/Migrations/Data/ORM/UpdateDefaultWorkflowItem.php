<?php

namespace OroCRM\Bundle\MagentoBundle\Migrations\Data\ORM;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\WorkflowBundle\Model\WorkflowAwareManager;
use Oro\Bundle\WorkflowBundle\Model\WorkflowManager;

use OroCRM\Bundle\MagentoBundle\Entity\Cart;
use OroCRM\Bundle\MagentoBundle\Entity\Order;

class UpdateDefaultWorkflowItem extends AbstractFixture implements ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $workflowAwareManager = new WorkflowAwareManager($this->container->get('oro_workflow.manager'));

        $workflowAwareManager->setWorkflowName('b2c_flow_abandoned_shopping_cart');
        $shoppingCarts = $manager->getRepository(Cart::class)->findAll();
        foreach ($shoppingCarts as $shoppingCart) {
            if (!$workflowAwareManager->getWorkflowItem($shoppingCart)) {
                $workflowAwareManager->startWorkflow($shoppingCart);
            }
        }

        $workflowAwareManager->setWorkflowName('b2c_flow_order_follow_up');
        $orders = $manager->getRepository(Order::class)->findAll();
        foreach ($orders as $order) {
            if (!$workflowAwareManager->getWorkflowItem($order)) {
                $workflowAwareManager->startWorkflow($order);
            }
        }
    }
}
