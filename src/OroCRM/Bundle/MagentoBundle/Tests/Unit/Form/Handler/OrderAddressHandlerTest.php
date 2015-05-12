<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\Form\Handler;

use OroCRM\Bundle\MagentoBundle\Entity\OrderAddress;
use OroCRM\Bundle\MagentoBundle\Form\Handler\OrderAddressHandler;

class OrderAddressHandlerTest extends AbstractHandlerTest
{
    protected function setUp()
    {
        $this->form = $this->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->getMock();

        $this->request = $this->getMockBuilder('Symfony\Component\HttpFoundation\Request')
            ->disableOriginalConstructor()
            ->getMock();

        $registry = $this->getMock('Symfony\Bridge\Doctrine\RegistryInterface');

        $this->manager = $this->getMock('Doctrine\Common\Persistence\ObjectManager');

        $registry->expects($this->once())
            ->method('getManager')
            ->will($this->returnValue($this->manager));

        $security     = $this->getMockBuilder('Symfony\Component\Security\Core\SecurityContext')
            ->setMethods(['getToken'])
            ->disableOriginalConstructor()
            ->getMock();
        $token        = $this->getMockBuilder('Symfony\Component\Security\Core\Authentication\Token\Token')
            ->setMethods(['getOrganizationContext'])
            ->disableOriginalConstructor()
            ->getMock();
        $organization = $this->getMockBuilder('Oro\Bundle\OrganizationBundle\Entity\Organization')
            ->disableOriginalConstructor()
            ->getMock();

        $token->expects($this->once())
            ->method('getOrganizationContext')
            ->will($this->returnValue($organization));

        $security->expects($this->once())
            ->method('getToken')
            ->will($this->returnValue($token));

        $this->entity  = new OrderAddress();
        $this->handler = new OrderAddressHandler($this->form, $this->request, $registry, $security);
    }
}
