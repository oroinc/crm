<?php

namespace Oro\Bundle\MagentoBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\BatchBundle\ORM\Query\BufferedIdentityQueryResultIterator;
use Oro\Bundle\ChannelBundle\Entity\LifetimeValueHistory;
use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\EntityBundle\Provider\EntityNameResolver;
use Oro\Bundle\MagentoBundle\Entity\Customer;
use Oro\Bundle\SalesBundle\Entity\Customer as CustomerAssociation;
use Oro\Bundle\SalesBundle\Entity\Manager\AccountCustomerManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

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

        $iterator = new BufferedIdentityQueryResultIterator($qb);
        $iterator->setBufferSize(self::BATCH_SIZE);
        $iterationCount = 0;

        foreach ($iterator as $entity) {
            $iterationCount++;
            $account = $entity->getAccount();
            if (!$account) {
                $account = $this->createAccount($entity);
                $entity->setAccount($account);

                $manager->persist($account);
                $manager->persist($entity);
            }

            $customerAssociation = new CustomerAssociation();
            $customerAssociation->setTarget($account, $entity);
            $manager->persist($customerAssociation);

            if ($iterationCount % self::BATCH_SIZE === 0) {
                $manager->flush();
                $this->clear($manager);
            }
        }

        if ($iterationCount > 0) {
            $manager->flush();
            $manager->clear();
        }
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
        $manager->clear(Contact::class);
        $manager->clear(Account::class);
        $manager->clear(LifetimeValueHistory::class);
    }
}
