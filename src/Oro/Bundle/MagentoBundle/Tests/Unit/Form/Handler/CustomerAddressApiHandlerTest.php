<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Form\Handler;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\MagentoBundle\Entity\Address;
use Oro\Bundle\MagentoBundle\Form\Handler\CustomerAddressApiHandler;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class CustomerAddressApiHandlerTest extends AbstractHandlerTest
{
    protected function setUp(): void
    {
        $this->form = $this->createMock(Form::class);
        $this->request = new Request();
        $requestStack = new RequestStack();
        $requestStack->push($this->request);
        $registry = $this->createMock(ManagerRegistry::class);
        $tokenAccessor = $this->createMock(TokenAccessorInterface::class);
        $organization = $this->createMock(Organization::class);
        $tokenAccessor->expects($this->once())
            ->method('getOrganization')
            ->will($this->returnValue($organization));

        $this->manager = $this->createMock(ObjectManager::class);

        $registry->expects($this->once())
            ->method('getManager')
            ->will($this->returnValue($this->manager));

        $this->entity  = new Address();
        $this->handler = new CustomerAddressApiHandler($this->form, $requestStack, $registry, $tokenAccessor);
    }
}
