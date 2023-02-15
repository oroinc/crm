<?php

namespace Oro\Bundle\SalesBundle\Api\Processor;

use Doctrine\ORM\Query\Expr\Join;
use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\ApiBundle\Config\EntityDefinitionConfig;
use Oro\Bundle\ApiBundle\Config\EntityDefinitionFieldConfig;
use Oro\Bundle\ApiBundle\Processor\GetConfig\ConfigContext;
use Oro\Bundle\ApiBundle\Util\ConfigUtil;
use Oro\Bundle\ApiBundle\Util\DoctrineHelper;
use Oro\Bundle\SalesBundle\Api\AccountCustomerAssociationProvider;
use Oro\Bundle\SalesBundle\Entity\Customer;
use Oro\Component\ChainProcessor\ContextInterface;
use Oro\Component\ChainProcessor\ProcessorInterface;

/**
 * Adds associations with customer entities to the account entity.
 * Adds association with the account entity to all customer entities.
 */
class AddAccountCustomerAssociations implements ProcessorInterface
{
    private const ACCOUNT_ASSOCIATION_NAME = 'account';

    private AccountCustomerAssociationProvider $accountCustomerAssociationProvider;
    private DoctrineHelper $doctrineHelper;

    public function __construct(
        AccountCustomerAssociationProvider $accountCustomerAssociationProvider,
        DoctrineHelper $doctrineHelper
    ) {
        $this->accountCustomerAssociationProvider = $accountCustomerAssociationProvider;
        $this->doctrineHelper = $doctrineHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContextInterface $context): void
    {
        /** @var ConfigContext $context */

        $entityClass = $context->getClassName();

        if (Account::class === $entityClass) {
            $customerAssociations = $this->accountCustomerAssociationProvider->getAccountCustomerAssociations(
                $context->getVersion(),
                $context->getRequestType()
            );
            if ($customerAssociations) {
                $definition = $context->getResult();
                foreach ($customerAssociations as $associationName => $customerAssociation) {
                    $this->addAccountCustomersAssociation(
                        $definition,
                        $associationName,
                        $customerAssociation['className'],
                        $customerAssociation['associationName']
                    );
                }
            }
        }

        if ($this->accountCustomerAssociationProvider->isCustomerEntity($entityClass)) {
            $this->addCustomerAccountAssociation($context->getResult(), $entityClass, self::ACCOUNT_ASSOCIATION_NAME);
        }
    }

    private function addAccountCustomersAssociation(
        EntityDefinitionConfig $definition,
        string $associationName,
        string $customerEntityClass,
        string $customerAssociationName
    ): void {
        if ($definition->hasField($associationName)
            && !$this->isAccountCustomersAssociation($definition->getField($associationName), $customerEntityClass)
        ) {
            throw new \RuntimeException(sprintf(
                'The customer association "%2$s" cannot be added to "%1$s"'
                . ' because an association with this name already exists.'
                . ' To rename the association to the "%3$s" customer entity'
                . ' use "oro_sales.api.customer_association_names" configuration option.'
                . ' For example:%4$soro_sales:%4$s    api:%4$s        customer_association_names:%4$s'
                . '            \'%3$s\': \'newName\'',
                Account::class,
                $associationName,
                $customerEntityClass,
                "\n"
            ));
        }

        $association = $definition->getOrAddField($associationName);
        $association->setTargetClass($customerEntityClass);
        $association->setTargetType(ConfigUtil::TO_MANY);
        $association->setPropertyPath(ConfigUtil::IGNORE_PROPERTY_PATH);
        if (!$association->isExcluded() && null === $association->getAssociationQuery()) {
            $association->setAssociationQuery(
                $this->doctrineHelper
                    ->createQueryBuilder($customerEntityClass, 'r')
                    ->innerJoin(Customer::class, 'ca', Join::WITH, sprintf('ca.%s = r', $customerAssociationName))
                    ->innerJoin('ca.account', 'e')
            );
        }
    }

    private function isAccountCustomersAssociation(
        EntityDefinitionFieldConfig $field,
        string $customerEntityClass
    ): bool {
        $targetClass = $field->getTargetClass();
        if ($targetClass && $customerEntityClass !== $targetClass) {
            return false;
        }
        $targetType = $field->getTargetType();
        if ($targetType && ConfigUtil::TO_MANY !== $targetType) {
            return false;
        }
        $propertyPath = $field->getPropertyPath();
        if ($propertyPath && ConfigUtil::IGNORE_PROPERTY_PATH !== $propertyPath) {
            return false;
        }

        return true;
    }

    private function addCustomerAccountAssociation(
        EntityDefinitionConfig $definition,
        string $customerEntityClass,
        string $associationName
    ): void {
        if ($definition->hasField($associationName)) {
            $targetClass = $definition->getField($associationName)->getTargetClass();
            if ($targetClass && Account::class !== $targetClass) {
                throw new \RuntimeException(sprintf(
                    'The association "%s" cannot be added to "%s"'
                    . ' because an association with this name already exists.',
                    $associationName,
                    $customerEntityClass
                ));
            }
        }

        $metadata = $this->doctrineHelper->getEntityMetadataForClass($customerEntityClass);
        if (!$metadata->hasAssociation($associationName)) {
            $association = $definition->getOrAddField($associationName);
            $association->setTargetClass(Account::class);
            $association->setTargetType(ConfigUtil::TO_ONE);
            $association->setPropertyPath(ConfigUtil::IGNORE_PROPERTY_PATH);
            if (!$association->isExcluded() && null === $association->getAssociationQuery()) {
                $customerAssociationName = $this->accountCustomerAssociationProvider
                    ->getCustomerTargetAssociationName($customerEntityClass);
                $association->setAssociationQuery(
                    $this->doctrineHelper
                        ->createQueryBuilder(Account::class, 'r')
                        ->innerJoin(Customer::class, 'ca', Join::WITH, 'ca.account = r')
                        ->innerJoin('ca.' . $customerAssociationName, 'e')
                );
            }
        }
    }
}
