<?php

namespace Oro\Bundle\SalesBundle\Tests\Functional\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use Oro\Bundle\SalesBundle\Entity\Customer;
use Oro\Bundle\SalesBundle\Entity\Manager\AccountCustomerManager;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\TestFrameworkCRMBundle\Entity\TestCustomer1;
use Oro\Bundle\TestFrameworkCRMBundle\Entity\TestCustomer2;

class CustomerAssociationListenerTest extends WebTestCase
{
    protected function setUp(): void
    {
        $this->initClient();
        $this->loadEntities($this->getEntityManager());
    }

    /**
     * Tests that after creating customer targets entities
     * all of them have created related customer associations
     */
    public function testCreateCustomerTargets()
    {
        $em = $this->getEntityManager();
        $target1Repository = $em->getRepository(TestCustomer1::class);
        $target1Qb = $target1Repository->createQueryBuilder('tc1');
        $targets1 = $target1Qb
            ->select('tc1')
            ->getQuery()
            ->getResult();

        $target1Ids = array_map(
            function (TestCustomer1 $target) {
                return $target->getId();
            },
            $targets1
        );
        $target1Field = AccountCustomerManager::getCustomerTargetField(TestCustomer1::class);

        $target2Repository = $em->getRepository(TestCustomer2::class);
        $target2Qb = $target2Repository->createQueryBuilder('tc2');
        $targets2 = $target2Qb
            ->select('tc2')
            ->getQuery()
            ->getResult();

        $target2Ids = array_map(
            function (TestCustomer2 $target) {
                return $target->getId();
            },
            $targets2
        );
        $target2Field = AccountCustomerManager::getCustomerTargetField(TestCustomer2::class);

        $customerRepository = $em->getRepository(Customer::class);
        $customerQb = $customerRepository->createQueryBuilder('c');
        $customers = $customerQb
            ->select('c')
            ->where(sprintf('c.%s IN (:ids1)', $target1Field))
            ->orWhere(sprintf('c.%s IN (:ids2)', $target2Field))
            ->setParameters(['ids1' => $target1Ids, 'ids2' => $target2Ids])
            ->getQuery()
            ->getResult();

        $customerTargets = array_map(
            function (Customer $customer) {
                return $customer->getTarget();
            },
            $customers
        );

        $this->assertArrayIntersectEquals(
            $this->sortTargets(array_merge($targets2, $targets1)),
            $this->sortTargets($customerTargets)
        );
    }

    private function loadTestCustomerTarget1(EntityManagerInterface $em, string $name): void
    {
        $testCustomerTarget1 = new TestCustomer1();
        $testCustomerTarget1->setName($name);
        $em->persist($testCustomerTarget1);
    }

    private function loadTestCustomerTarget2(EntityManagerInterface $em, string $name): void
    {
        $testCustomerTarget2 = new TestCustomer2();
        $testCustomerTarget2->setName($name);
        $em->persist($testCustomerTarget2);
    }

    private function loadEntities(EntityManagerInterface $em): void
    {
        $name = 'test_%s_%s';

        foreach (range(1, 5) as $id) {
            $this->loadTestCustomerTarget1($em, sprintf($name, 1, $id));
            $this->loadTestCustomerTarget2($em, sprintf($name, 2, $id));
        }
        $em->flush();
    }

    private function sortTargets(array $targets): array
    {
        usort($targets, function (TestCustomer1|TestCustomer2 $item1, TestCustomer1|TestCustomer2 $item2): int {
            return strcmp($item1->getName(), $item2->getName());
        });

        return $targets;
    }

    private function getEntityManager(): EntityManagerInterface
    {
        return $this->getContainer()->get('doctrine')->getManagerForClass(Customer::class);
    }
}
