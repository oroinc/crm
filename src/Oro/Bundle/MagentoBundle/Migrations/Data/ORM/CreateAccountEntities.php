<?php

namespace Oro\Bundle\MagentoBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

use Oro\Bundle\EntityBundle\Provider\EntityNameResolver;
use Oro\Bundle\BatchBundle\ORM\Query\BufferedQueryResultIterator;
use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\MagentoBundle\Entity\Customer;
use Oro\Bundle\SalesBundle\Entity\Customer as CustomerAssociation;
use Oro\Bundle\SalesBundle\Entity\Manager\AccountCustomerManager;

class CreateAccountEntities extends AbstractFixture implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    const BATCH_SIZE = 500;

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        if (!$this->container->hasParameter('installed') || !$this->container->getParameter('installed')) {
            return;
        }

        $field = AccountCustomerManager::getCustomerTargetField(Customer::class);

        $qb = $manager->getRepository(Customer::class)
            ->createQueryBuilder('c')
            ->leftJoin(CustomerAssociation::class, 'ca', 'WITH', sprintf('ca.%s = c', $field))
            ->where('ca.id IS NULL');

        $iterator = new BufferedQueryResultIterator($qb);
        $iterator->setBufferSize(self::BATCH_SIZE);
        $objects = [];
        $iteration = 0;
        foreach ($iterator as $entity) {
            $iteration++;
            $account = $entity->getAccount();
            if (!$account) {
                $account = $this->createAccount($entity);
                $entity->setAccount($account);

                $objects[] = $account;
                $objects[] = $entity;
                $manager->persist($account);
                $manager->persist($entity);
            }

            $customerAssociation = new CustomerAssociation();
            $customerAssociation->setTarget($account, $entity);
            $manager->persist($customerAssociation);

            $objects[] = $customerAssociation;
            if (0 === $iteration % self::BATCH_SIZE) {
                $manager->flush($objects);
                $this->clear($manager);
                $objects = [];
            }
        }
        if ($objects) {
            $manager->flush($objects);
        }
        $this->clear($manager);
    }

    /**
     * @param Customer $entity
     *
     * @return Account
     */
    protected function createAccount(Customer $entity)
    {
        $account = new Account();
        $account->setName($this->getEntityNameResolver()->getName($entity));
        $account->setOrganization($entity->getOrganization());
        $account->setOwner($entity->getOwner());

        return $account;
    }

    /**
     * @return EntityNameResolver
     */
    protected function getEntityNameResolver()
    {
        return $this->container->get('oro_entity.entity_name_resolver');
    }
    /**
     * @param ObjectManager $manager
     */
    protected function clear($manager)
    {
        $manager->clear(CustomerAssociation::class);
        $manager->clear(Customer::class);
        $manager->clear(Account::class);
    }
}
