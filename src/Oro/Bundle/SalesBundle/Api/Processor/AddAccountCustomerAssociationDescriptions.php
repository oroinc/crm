<?php

namespace Oro\Bundle\SalesBundle\Api\Processor;

use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\ApiBundle\ApiDoc\EntityNameProvider;
use Oro\Bundle\ApiBundle\ApiDoc\ResourceDocParserInterface;
use Oro\Bundle\ApiBundle\Config\EntityDefinitionConfig;
use Oro\Bundle\ApiBundle\Processor\GetConfig\CompleteDescriptions\ResourceDocParserProvider;
use Oro\Bundle\ApiBundle\Processor\GetConfig\ConfigContext;
use Oro\Bundle\ApiBundle\Request\ApiAction;
use Oro\Bundle\ApiBundle\Request\RequestType;
use Oro\Bundle\ApiBundle\Request\ValueNormalizer;
use Oro\Bundle\ApiBundle\Util\ValueNormalizerUtil;
use Oro\Bundle\SalesBundle\Api\AccountCustomerAssociationProvider;
use Oro\Component\ChainProcessor\ContextInterface;
use Oro\Component\ChainProcessor\ProcessorInterface;

/**
 * Adds human-readable descriptions for associations with customer entities.
 */
class AddAccountCustomerAssociationDescriptions implements ProcessorInterface
{
    private const ACCOUNT_ASSOCIATION_NAME = 'account';

    private const ACCOUNT_ASSOCIATION_DOC_RESOURCE =
        '@OroSalesBundle/Resources/doc/api/account_association.md';
    private const CUSTOMER_ENTITY = '%customer_entity%';
    private const ACCOUNT_ASSOCIATION = '%account_association%';

    private const ACCOUNT_CUSTOMERS_ASSOCIATION_DOC_RESOURCE =
        '@OroSalesBundle/Resources/doc/api/account_customers_association.md';
    private const CUSTOMERS_ASSOCIATION = '%customers_association%';
    private const CUSTOMER_ENTITY_NAME = '%customer_entity_name%';
    private const CUSTOMER_ENTITY_PLURAL_NAME = '%customer_entity_plural_name%';
    private const CUSTOMER_ENTITY_TYPE = '%customer_entity_type%';

    private AccountCustomerAssociationProvider $accountCustomerAssociationProvider;
    private ValueNormalizer $valueNormalizer;
    private ResourceDocParserProvider $resourceDocParserProvider;
    private EntityNameProvider $entityNameProvider;

    public function __construct(
        AccountCustomerAssociationProvider $accountCustomerAssociationProvider,
        ValueNormalizer $valueNormalizer,
        ResourceDocParserProvider $resourceDocParserProvider,
        EntityNameProvider $entityNameProvider
    ) {
        $this->accountCustomerAssociationProvider = $accountCustomerAssociationProvider;
        $this->resourceDocParserProvider = $resourceDocParserProvider;
        $this->valueNormalizer = $valueNormalizer;
        $this->entityNameProvider = $entityNameProvider;
    }

    #[\Override]
    public function process(ContextInterface $context): void
    {
        /** @var ConfigContext $context */

        $targetAction = $context->getTargetAction();
        if (!$targetAction || ApiAction::OPTIONS === $targetAction) {
            return;
        }

        $version = $context->getVersion();
        $requestType = $context->getRequestType();
        $definition = $context->getResult();
        $associationName = $context->getAssociationName();
        $entityClass = $associationName ? $context->getParentClassName() : $context->getClassName();

        if (Account::class === $entityClass) {
            $customerAssociations = $this->accountCustomerAssociationProvider->getAccountCustomerAssociations(
                $version,
                $requestType
            );
            if ($customerAssociations) {
                $this->addAccountCustomersAssociationDescriptions(
                    $definition,
                    $requestType,
                    $targetAction,
                    $associationName,
                    $customerAssociations
                );
            }
        }

        if ($associationName) {
            $this->setDescriptionForCustomerAccountField(
                $definition,
                $requestType,
                $targetAction,
                $definition->getResourceClass()
            );
            $this->setDescriptionsForCustomerFields(
                $definition,
                $requestType,
                $targetAction,
                $this->accountCustomerAssociationProvider->getAccountCustomerAssociations($version, $requestType)
            );
            if (self::ACCOUNT_ASSOCIATION_NAME === $associationName && !$definition->hasDocumentation()) {
                $this->setDescriptionsForCustomerAccountSubresource(
                    $definition,
                    $requestType,
                    $entityClass,
                    $targetAction
                );
            }
        } else {
            $this->setDescriptionForCustomerAccountField(
                $definition,
                $requestType,
                $targetAction,
                $context->getClassName()
            );
        }
    }

    private function addAccountCustomersAssociationDescriptions(
        EntityDefinitionConfig $definition,
        RequestType $requestType,
        string $targetAction,
        ?string $associationName,
        array $customerAssociations
    ): void {
        if (!$associationName) {
            $this->setDescriptionsForCustomerFields(
                $definition,
                $requestType,
                $targetAction,
                $customerAssociations
            );
        } elseif (isset($customerAssociations[$associationName]) && !$definition->hasDocumentation()) {
            $this->setDescriptionsForCustomerSubresource(
                $definition,
                $requestType,
                $customerAssociations[$associationName]['className'],
                $targetAction
            );
        }
    }

    private function setDescriptionsForCustomerFields(
        EntityDefinitionConfig $definition,
        RequestType $requestType,
        string $targetAction,
        array $customerAssociations
    ): void {
        $associationDocumentationTemplate = $this->getAssociationDocumentationTemplate(
            $this->getDocumentationParser($requestType, self::ACCOUNT_CUSTOMERS_ASSOCIATION_DOC_RESOURCE),
            Account::class,
            self::CUSTOMERS_ASSOCIATION,
            $targetAction
        );

        foreach ($customerAssociations as $associationName => $customerAssociation) {
            $customerAssociationDefinition = $definition->getField($associationName);
            if (null === $customerAssociationDefinition || $customerAssociationDefinition->hasDescription()) {
                continue;
            }
            $customerAssociationDefinition->setDescription(strtr($associationDocumentationTemplate, [
                self::CUSTOMER_ENTITY_PLURAL_NAME => $this->entityNameProvider->getEntityPluralName(
                    $customerAssociation['className'],
                    true
                )
            ]));
        }
    }

    private function setDescriptionsForCustomerSubresource(
        EntityDefinitionConfig $definition,
        RequestType $requestType,
        string $customerEntityClass,
        string $targetAction
    ): void {
        $docParser = $this->getDocumentationParser($requestType, self::ACCOUNT_CUSTOMERS_ASSOCIATION_DOC_RESOURCE);
        $subresourceDocumentationTemplate = $docParser->getSubresourceDocumentation(
            Account::class,
            self::CUSTOMERS_ASSOCIATION,
            $targetAction
        );

        $definition->setDocumentation(strtr($subresourceDocumentationTemplate, [
            self::CUSTOMER_ENTITY_PLURAL_NAME => $this->entityNameProvider->getEntityPluralName(
                $customerEntityClass,
                true
            ),
            self::CUSTOMER_ENTITY_TYPE => $this->getEntityType($customerEntityClass, $requestType)
        ]));
    }

    private function setDescriptionForCustomerAccountField(
        EntityDefinitionConfig $definition,
        RequestType $requestType,
        string $targetAction,
        string $customerEntityClass
    ): void {
        if (!$this->accountCustomerAssociationProvider->isCustomerEntity($customerEntityClass)) {
            return;
        }

        $accountAssociationDefinition = $definition->getField(self::ACCOUNT_ASSOCIATION_NAME);
        if (null === $accountAssociationDefinition || $accountAssociationDefinition->hasDescription()) {
            return;
        }

        $associationDocumentationTemplate = $this->getAssociationDocumentationTemplate(
            $this->getDocumentationParser($requestType, self::ACCOUNT_ASSOCIATION_DOC_RESOURCE),
            self::CUSTOMER_ENTITY,
            self::ACCOUNT_ASSOCIATION,
            $targetAction
        );
        $accountAssociationDefinition->setDescription(strtr($associationDocumentationTemplate, [
            self::CUSTOMER_ENTITY_NAME => $this->entityNameProvider->getEntityName($customerEntityClass, true)
        ]));
    }

    private function setDescriptionsForCustomerAccountSubresource(
        EntityDefinitionConfig $definition,
        RequestType $requestType,
        string $customerEntityClass,
        string $targetAction
    ): void {
        if (!$this->accountCustomerAssociationProvider->isCustomerEntity($customerEntityClass)) {
            return;
        }

        $docParser = $this->getDocumentationParser($requestType, self::ACCOUNT_ASSOCIATION_DOC_RESOURCE);
        $subresourceDocumentationTemplate = $docParser->getSubresourceDocumentation(
            self::CUSTOMER_ENTITY,
            self::ACCOUNT_ASSOCIATION,
            $targetAction
        );
        $definition->setDocumentation(strtr($subresourceDocumentationTemplate, [
            self::CUSTOMER_ENTITY_NAME => $this->entityNameProvider->getEntityName($customerEntityClass, true)
        ]));
    }

    private function getEntityType(string $entityClass, RequestType $requestType): string
    {
        return ValueNormalizerUtil::convertToEntityType($this->valueNormalizer, $entityClass, $requestType);
    }

    private function getDocumentationParser(
        RequestType $requestType,
        string $documentationResource
    ): ResourceDocParserInterface {
        $docParser = $this->resourceDocParserProvider->getResourceDocParser($requestType);
        $docParser->registerDocumentationResource($documentationResource);

        return $docParser;
    }

    private function getAssociationDocumentationTemplate(
        ResourceDocParserInterface $docParser,
        string $className,
        string $fieldName,
        string $targetAction
    ): ?string {
        return $docParser->getFieldDocumentation($className, $fieldName, $targetAction)
            ?: $docParser->getFieldDocumentation($className, $fieldName);
    }
}
