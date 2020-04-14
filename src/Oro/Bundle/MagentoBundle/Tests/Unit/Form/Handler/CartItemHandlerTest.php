<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Form\Handler;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\MagentoBundle\Entity\CartItem;
use Oro\Bundle\MagentoBundle\Form\Handler\CartItemHandler;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class CartItemHandlerTest extends AbstractHandlerTest
{
    protected function setUp(): void
    {
        $this->form = $this->createMock(Form::class);
        $this->request = new Request();
        $requestStack = new RequestStack();
        $requestStack->push($this->request);
        $registry = $this->createMock(ManagerRegistry::class);
        $this->manager = $this->createMock(ObjectManager::class);

        $registry->expects($this->once())
            ->method('getManager')
            ->will($this->returnValue($this->manager));

        $this->entity  = new CartItem();
        $this->handler = new CartItemHandler($this->form, $requestStack, $registry);
    }
}
