<?php

namespace Oro\Bundle\SalesBundle\Tests\Unit\Api\Processor;

use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\ApiBundle\Processor\CollectSubresources\CollectSubresourcesContext;
use Oro\Bundle\ApiBundle\Request\ApiAction;
use Oro\Bundle\ApiBundle\Request\ApiResource;
use Oro\Bundle\ApiBundle\Request\ApiResourceSubresources;
use Oro\Bundle\ApiBundle\Request\ApiResourceSubresourcesCollection;
use Oro\Bundle\ApiBundle\Request\ApiSubresource;
use Oro\Bundle\ApiBundle\Request\RequestType;
use Oro\Bundle\SalesBundle\Api\AccountCustomerAssociationProvider;
use Oro\Bundle\SalesBundle\Api\Processor\ExcludeChangeCustomerSubresources;

class ExcludeChangeCustomerSubresourcesTest extends \PHPUnit\Framework\TestCase
{
    /** @var AccountCustomerAssociationProvider|\PHPUnit\Framework\MockObject\MockObject */
    private $accountCustomerAssociationProvider;

    /** @var ExcludeChangeCustomerSubresources */
    private $processor;

    /** @var CollectSubresourcesContext */
    private $context;

    protected function setUp(): void
    {
        $this->accountCustomerAssociationProvider = $this->createMock(AccountCustomerAssociationProvider::class);

        $this->processor = new ExcludeChangeCustomerSubresources($this->accountCustomerAssociationProvider);

        $this->context = new CollectSubresourcesContext();
        $this->context->getRequestType()->add(RequestType::REST);
        $this->context->setVersion('1.1');
    }

    private function getApiResourceSubresources(ApiResource $resource): ApiResourceSubresourcesCollection
    {
        $entitySubresources = new ApiResourceSubresources($resource->getEntityClass());
        $subresources = new ApiResourceSubresourcesCollection();
        $subresources->add($entitySubresources);

        return $subresources;
    }

    public function testProcessForDisabledSubresources(): void
    {
        $resource = new ApiResource(Account::class);
        $resource->setExcludedActions([ApiAction::GET_SUBRESOURCE]);
        $subresources = $this->getApiResourceSubresources($resource);

        $this->accountCustomerAssociationProvider->expects(self::never())
            ->method('getAccountCustomerAssociations');

        $this->context->setResources([$resource]);
        $this->context->setAccessibleResources([]);
        $this->context->setResult($subresources);
        $this->processor->process($this->context);
    }

    public function testProcessWhenNoSubresources(): void
    {
        $resource = new ApiResource(Account::class);

        $this->accountCustomerAssociationProvider->expects(self::never())
            ->method('getAccountCustomerAssociations');

        $this->context->setResources([$resource]);
        $this->context->setAccessibleResources([]);
        $this->context->setResult(new ApiResourceSubresourcesCollection());
        $this->processor->process($this->context);
    }

    public function testProcessWhenNoCustomerAssociations(): void
    {
        $resource = new ApiResource(Account::class);
        $subresources = $this->getApiResourceSubresources($resource);

        $this->accountCustomerAssociationProvider->expects(self::once())
            ->method('getAccountCustomerAssociations')
            ->with($this->context->getVersion(), $this->context->getRequestType())
            ->willReturn([]);

        $this->context->setResources([$resource]);
        $this->context->setAccessibleResources([]);
        $this->context->setResult($subresources);
        $this->processor->process($this->context);
    }

    public function testProcessWhenNoCustomerSubresource(): void
    {
        $resource = new ApiResource(Account::class);
        $subresources = $this->getApiResourceSubresources($resource);

        $this->accountCustomerAssociationProvider->expects(self::once())
            ->method('getAccountCustomerAssociations')
            ->with($this->context->getVersion(), $this->context->getRequestType())
            ->willReturn([
                'customers' => ['className' => 'Test\Customer', 'associationName' => 'association1']
            ]);

        $this->context->setResources([$resource]);
        $this->context->setAccessibleResources([]);
        $this->context->setResult($subresources);
        $this->processor->process($this->context);
    }

    public function testProcessWithCustomerSubresource(): void
    {
        $resource = new ApiResource(Account::class);
        $subresources = $this->getApiResourceSubresources($resource);
        $customersSubresource = new ApiSubresource();
        $customersSubresource->addExcludedAction(ApiAction::UPDATE_SUBRESOURCE);
        $customersSubresource->addExcludedAction(ApiAction::ADD_SUBRESOURCE);
        $customersSubresource->addExcludedAction(ApiAction::DELETE_SUBRESOURCE);
        $subresources->get(Account::class)->addSubresource('customers', $customersSubresource);

        $this->accountCustomerAssociationProvider->expects(self::once())
            ->method('getAccountCustomerAssociations')
            ->with($this->context->getVersion(), $this->context->getRequestType())
            ->willReturn([
                'customers' => ['className' => 'Test\Customer', 'associationName' => 'association1']
            ]);

        $this->context->setResources([$resource]);
        $this->context->setAccessibleResources([]);
        $this->context->setResult($subresources);
        $this->processor->process($this->context);

        self::assertEquals(
            [
                ApiAction::UPDATE_SUBRESOURCE,
                ApiAction::ADD_SUBRESOURCE,
                ApiAction::DELETE_SUBRESOURCE,
                ApiAction::UPDATE_RELATIONSHIP,
                ApiAction::ADD_RELATIONSHIP,
                ApiAction::DELETE_RELATIONSHIP
            ],
            $customersSubresource->getExcludedActions()
        );
    }
}
