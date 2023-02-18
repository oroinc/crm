<?php

namespace Oro\Bundle\SalesBundle\Api\Processor;

use Doctrine\ORM\EntityManagerInterface;
use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\ApiBundle\Form\FormUtil;
use Oro\Bundle\ApiBundle\Processor\CustomizeFormData\CustomizeFormDataContext;
use Oro\Bundle\ApiBundle\Util\DoctrineHelper;
use Oro\Bundle\SalesBundle\Entity\Customer;
use Oro\Bundle\SalesBundle\Entity\Manager\AccountCustomerManager;
use Oro\Bundle\SalesBundle\Provider\Customer\ConfigProvider;
use Oro\Component\ChainProcessor\ContextInterface;
use Oro\Component\ChainProcessor\ProcessorInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

/**
 * Handles the account association for customer entities.
 */
class HandleCustomerAccountAssociation implements ProcessorInterface, ServiceSubscriberInterface
{
    private const ACCOUNT_FIELD_NAME = 'account';

    private DoctrineHelper $doctrineHelper;
    private ContainerInterface $container;

    public function __construct(DoctrineHelper $doctrineHelper, ContainerInterface $container)
    {
        $this->doctrineHelper = $doctrineHelper;
        $this->container = $container;
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedServices(): array
    {
        return [
            ConfigProvider::class,
            AccountCustomerManager::class
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function process(ContextInterface $context): void
    {
        /** @var CustomizeFormDataContext $context */

        $entityClass = $context->getClassName();
        if (!$this->getConfigProvider()->isCustomerClass($entityClass)) {
            return;
        }

        $form = $context->getForm();
        if (!$form->has(self::ACCOUNT_FIELD_NAME)) {
            return;
        }

        $accountField = $form->get(self::ACCOUNT_FIELD_NAME);
        if (!FormUtil::isSubmittedAndValid($accountField)) {
            return;
        }

        $this->changeCustomerAssociation($form->getData(), $accountField);
    }

    private function changeCustomerAssociation(object $customer, FormInterface $accountField): void
    {
        $account = $accountField->getData();
        if ($this->doctrineHelper->isNewEntity($customer)) {
            $this->createCustomerAssociation(
                $customer,
                $account ?? $this->getAccountCustomerManager()->createAccountForTarget($customer)
            );
        } elseif (null !== $account) {
            $association = $this->getAccountCustomerManager()->getAccountCustomerByTarget($customer, false);
            if (null === $association) {
                $this->createCustomerAssociation($customer, $account);
            } else {
                $association->setTarget($account, $customer);
            }
        } else {
            FormUtil::addFormConstraintViolation($accountField, new NotBlank());
        }
    }

    private function createCustomerAssociation(object $customer, Account $account): void
    {
        $association = new Customer();
        $association->setTarget($account, $customer);
        $this->getEntityManager()->persist($association);
    }

    private function getEntityManager(): EntityManagerInterface
    {
        return $this->doctrineHelper->getEntityManagerForClass(Customer::class);
    }

    private function getConfigProvider(): ConfigProvider
    {
        return $this->container->get(ConfigProvider::class);
    }

    private function getAccountCustomerManager(): AccountCustomerManager
    {
        return $this->container->get(AccountCustomerManager::class);
    }
}
