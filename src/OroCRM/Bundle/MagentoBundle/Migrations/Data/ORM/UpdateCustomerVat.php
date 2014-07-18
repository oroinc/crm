<?php

namespace OroCRM\Bundle\MagentoBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

use Oro\Bundle\BatchBundle\ORM\Query\BufferedQueryResultIterator;

class UpdateCustomerVat extends AbstractFixture
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        /** @var QueryBuilder $queryBuilder */
        /** @var EntityRepository $repository */
        $repository = $manager->getRepository('OroCRMMagentoBundle:Customer');
        $queryBuilder = $repository->createQueryBuilder('customer');

        $queryBuilder
            ->select('customer.id, customer.vat')
            ->where($queryBuilder->expr()->isNotNull('customer.vat'));

        $query = 'UPDATE OroCRMMagentoBundle:Customer customer SET customer.vat = :vat WHERE customer.id = :id';

        // update lifetime for all customers
        $iterator = new BufferedQueryResultIterator($queryBuilder);
        foreach ($iterator as $row) {
            $manager->createQuery($query)
                ->setParameter('id', $row['id'])
                ->setParameter('vat', $row['vat'] / 100)
                ->execute();
        }
    }
}
