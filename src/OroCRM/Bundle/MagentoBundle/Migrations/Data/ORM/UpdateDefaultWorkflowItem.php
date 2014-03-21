<?php

namespace OroCRM\Bundle\MagentoBundle\Migrations\Data\ORM;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityRepository;

use Oro\Bundle\WorkflowBundle\Model\WorkflowManager;

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
        /** @var WorkflowManager $workflowManager */
        $workflowManager = $this->container->get('oro_workflow.manager');

        /** @var EntityRepository $shoppingCartRepository */
        $shoppingCartRepository = $manager->getRepository('OroCRMMagentoBundle:Cart');
        $shoppingCarts = $shoppingCartRepository->createQueryBuilder('cart')
            ->where('cart.workflowItem IS NULL')
            ->getQuery()
            ->execute();
        foreach ($shoppingCarts as $shoppingCart) {
            $workflowManager->startWorkflow('b2c_flow_abandoned_shopping_cart', $shoppingCart);
        }

        /** @var EntityRepository $orderRepository */
        $orderRepository = $manager->getRepository('OroCRMMagentoBundle:Order');
        $orders = $orderRepository->createQueryBuilder('orderEntity')
            ->where('orderEntity.workflowItem IS NULL')
            ->getQuery()
            ->execute();
        foreach ($orders as $order) {
            $workflowManager->startWorkflow('b2c_flow_order_follow_up', $order);
        }
    }
}
