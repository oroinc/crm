<?php

namespace OroCRM\Bundle\SalesBundle\Model;

use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\EntityBundle\Provider\EntityFieldProvider;

use OroCRM\Bundle\AccountBundle\Entity\Account;
use OroCRM\Bundle\SalesBundle\Entity\B2bCustomer;
use OroCRM\Bundle\SalesBundle\Entity\Lead;

class B2bGuesser
{
    /**
     * @var ObjectManager
     */
    protected $manager;
    
    /**
     * @var EntityFieldProvider
     */
    protected $entityFieldProvider;

    /**
     * B2bGuesser constructor.
     *
     * @param ObjectManager $manager
     * @param EntityFieldProvider $entityFieldProvider
     */
    public function __construct(ObjectManager $manager, EntityFieldProvider $entityFieldProvider)
    {
        $this->manager = $manager;
        $this->entityFieldProvider = $entityFieldProvider;
    }

    /**
     * @param Lead $lead
     *
     * @return B2bCustomer
     */
    public function getCustomer(Lead $lead)
    {
        $customer = $lead->getCustomer();
        $customer = (null === $customer) ? $this->findCustomer($lead->getCompanyName()) : $customer;

        if ($customer) {
            return $customer;
        }

        return $this->createCustomer($lead);
    }

    /**
     * @param Lead $lead
     *
     * @return B2bCustomer
     */
    protected function createCustomer(Lead $lead)
    {
        $b2bCustomer = new B2bCustomer();
        $account = $this->getAccount($lead);
        $b2bCustomer->setName($lead->getCompanyName());
        $b2bCustomer->setDataChannel($lead->getDataChannel());
        $b2bCustomer->setAccount($account);

        $fields = $this->entityFieldProvider->getFields('OroCRMSalesBundle:B2bCustomer');
        foreach ($fields as $field) {
            if ($field['name'] === 'employees') {
                $b2bCustomer->setEmployees($lead->getNumberOfEmployees());
            }

            if ($field['name'] === 'website' && $lead->getWebsite()) {
                $b2bCustomer->setWebsite($lead->getWebsite());
            }
        }

        $this->manager->persist($b2bCustomer);
        $this->manager->flush();

        return $b2bCustomer;
    }

    protected function findCustomer($companyName)
    {
        $customer = $this->findCustomerByCompanyName($companyName);
        if (null !== $customer) {
            return $customer;
        }
        return $this->findCustomerByAccountName($companyName);
    }

    /**
     * @param string $companyName
     *
     * @return B2bCustomer|null
     */
    protected function findCustomerByCompanyName($companyName)
    {
        $repository = $this->manager->getRepository('OroCRMSalesBundle:B2bCustomer');

        $queryBuilder = $repository->createQueryBuilder('c');
        $result = $queryBuilder
            ->groupBy('c.id')
            ->where('c.name = :company_name')
            ->setParameter('company_name', $companyName)
            ->getQuery()
            ->getResult();

        $resultCount = count($result);

        return (!$resultCount || $resultCount > 1) ? null : reset($result);
    }

    /**
     * @param $companyName
     *
     * @return B2bCustomer|null
     */
    protected function findCustomerByAccountName($companyName)
    {
        $repository = $this->manager->getRepository('OroCRMSalesBundle:B2bCustomer');

        $queryBuilder = $repository->createQueryBuilder('c');
        $result = $queryBuilder
            ->innerJoin('OroCRMAccountBundle:Account', 'a', 'WITH', 'c.account = a')
            ->where('a.name = :company_name')
            ->setParameter('company_name', $companyName)
            ->getQuery()
            ->getResult();

        $resultCount = count($result);

        return (!$resultCount || $resultCount > 1) ? null : reset($result);
    }

    /**
     * @param Lead $lead
     *
     * @return Account
     */
    protected function getAccount(Lead $lead)
    {
        $account = $this->findAccountByCompanyName($lead->getCompanyName());
        if (null === $account) {
            $account = new Account();
            $account->setName($lead->getCompanyName());
        }
        return $account;
    }

    /**
     * @param string $companyName
     *
     * @return Account|null
     */
    protected function findAccountByCompanyName($companyName)
    {
        $repository = $this->manager->getRepository('OroCRMAccountBundle:Account');

        $result = $repository->createQueryBuilder('a')
            ->where('a.name = :company_name')
            ->setParameter('company_name', $companyName)
            ->getQuery()
            ->getResult();

        $resultCount = count($result);

        return (!$resultCount || $resultCount > 1) ? null : reset($result);
    }
}
