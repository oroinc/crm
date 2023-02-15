<?php

namespace Oro\Bundle\SalesBundle\Api\Processor;

use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\ApiBundle\Processor\CollectSubresources\CollectSubresourcesContext;
use Oro\Bundle\ApiBundle\Processor\CollectSubresources\SubresourceUtil;
use Oro\Bundle\ApiBundle\Request\ApiAction;
use Oro\Bundle\SalesBundle\Api\AccountCustomerAssociationProvider;
use Oro\Component\ChainProcessor\ContextInterface;
use Oro\Component\ChainProcessor\ProcessorInterface;

/**
 * Excludes the following actions for all customer associations for the account entity:
 * * update_relationship
 * * add_relationship
 * * delete_relationship
 */
class ExcludeChangeCustomerSubresources implements ProcessorInterface
{
    private AccountCustomerAssociationProvider $accountCustomerAssociationProvider;

    public function __construct(AccountCustomerAssociationProvider $accountCustomerAssociationProvider)
    {
        $this->accountCustomerAssociationProvider = $accountCustomerAssociationProvider;
    }

    /**
     * {@inheritDoc}
     */
    public function process(ContextInterface $context): void
    {
        /** @var CollectSubresourcesContext $context */

        $version = $context->getVersion();
        $requestType = $context->getRequestType();
        $subresources = $context->getResult();
        $resources = $context->getResources();
        foreach ($resources as $entityClass => $resource) {
            if (Account::class !== $entityClass || !SubresourceUtil::isSubresourcesEnabled($resource)) {
                continue;
            }
            $entitySubresources = $subresources->get($entityClass);
            if (null === $entitySubresources) {
                continue;
            }

            $customerAssociations = $this->accountCustomerAssociationProvider->getAccountCustomerAssociations(
                $version,
                $requestType
            );
            if (!$customerAssociations) {
                continue;
            }

            foreach ($customerAssociations as $associationName => $customerAssociation) {
                $associationSubresource = $entitySubresources->getSubresource($associationName);
                if (null !== $associationSubresource) {
                    $associationSubresource->addExcludedAction(ApiAction::UPDATE_RELATIONSHIP);
                    $associationSubresource->addExcludedAction(ApiAction::ADD_RELATIONSHIP);
                    $associationSubresource->addExcludedAction(ApiAction::DELETE_RELATIONSHIP);
                }
            }
        }
    }
}
