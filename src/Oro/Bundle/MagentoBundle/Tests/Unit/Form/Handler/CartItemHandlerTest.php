<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Form\Handler;

use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\MagentoBundle\Entity\CartItem;
use Oro\Bundle\MagentoBundle\Form\Handler\CartItemHandler;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class CartItemHandlerTest extends AbstractHandlerTest
{
    protected function setUp()
    {
        $this->form = $this->createMock(Form::class);
        $this->request = $this->createMock(Request::class);
        $requestStack = new RequestStack();
        $requestStack->push($this->request);
        $registry = $this->createMock(RegistryInterface::class);
        $this->manager = $this->createMock(ObjectManager::class);

        $registry->expects($this->once())
            ->method('getManager')
            ->will($this->returnValue($this->manager));

        $this->entity  = new CartItem();
        $this->handler = new CartItemHandler($this->form, $requestStack, $registry);
    }
}
