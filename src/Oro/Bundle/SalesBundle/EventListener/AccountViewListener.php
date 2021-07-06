<?php

namespace Oro\Bundle\SalesBundle\EventListener;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\SalesBundle\Entity\Customer;
use Oro\Bundle\SalesBundle\Entity\Manager\AccountCustomerManager;
use Oro\Bundle\SalesBundle\Provider\Customer\ConfigProvider;
use Oro\Bundle\UIBundle\Event\BeforeListRenderEvent;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Listener adds account information
 */
class AccountViewListener
{
    /** @var DoctrineHelper */
    protected $doctrineHelper;

    /** @var RequestStack */
    protected $requestStack;

    /** @var ConfigProvider */
    protected $configProvider;

    /** @var AccountCustomerManager */
    protected $accountCustomerManager;

    /** @var string */
    protected $entityClass;

    /**
     * @param DoctrineHelper         $doctrineHelper
     * @param RequestStack           $requestStack
     * @param ConfigProvider         $configProvider
     * @param AccountCustomerManager $accountCustomerManager
     * @param string                 $entityClass
     */
    public function __construct(
        DoctrineHelper $doctrineHelper,
        RequestStack $requestStack,
        ConfigProvider $configProvider,
        AccountCustomerManager $accountCustomerManager,
        $entityClass
    ) {
        $this->doctrineHelper = $doctrineHelper;
        $this->requestStack = $requestStack;
        $this->configProvider = $configProvider;
        $this->accountCustomerManager = $accountCustomerManager;
        $this->entityClass = $entityClass;
    }

    /**
     * {@inheritdoc}
     */
    public function onView(BeforeListRenderEvent $event)
    {
        // guard
        if (!$this->configProvider->isCustomerClass($this->entityClass)) {
            throw new \InvalidArgumentException(
                sprintf('%s is not a customer class.', $this->entityClass)
            );
        }

        $customer = $this->getEntityFromRequest();
        if (!$customer) {
            return;
        }

        $customerAssociation = $this->getCustomerAssociation($customer);
        if ($customerAssociation) {
            $account = $customerAssociation->getAccount();

            $template = $event->getEnvironment()->render(
                '@OroSales/Account/account_view.html.twig',
                ['account' => $account]
            );
            $event->getScrollData()->addSubBlockData(0, 0, $template);
        }
    }

    /**
     * @param object $customer
     * @param bool $throwExceptionOnNotFound
     *
     * @return Customer
     */
    protected function getCustomerAssociation($customer, $throwExceptionOnNotFound = true)
    {
        return $this->accountCustomerManager->getAccountCustomerByTarget($customer, $throwExceptionOnNotFound);
    }

    /**
     * @return object
     */
    protected function getEntityFromRequest()
    {
        $request = $this->requestStack->getCurrentRequest();

        if (!$request) {
            return null;
        }

        $customerId = filter_var($request->get('id'), FILTER_VALIDATE_INT);
        if (false === $customerId) {
            return null;
        }

        return $this->doctrineHelper->getEntityReference($this->entityClass, $customerId);
    }
}
