<?php

namespace OroCRM\Bundle\SalesBundle\Tests\Unit\Form\Handler;

use OroCRM\Bundle\ChannelBundle\Provider\RequestChannelProvider;
use OroCRM\Bundle\SalesBundle\Entity\B2bCustomer;
use OroCRM\Bundle\SalesBundle\Form\Handler\B2bCustomerHandler;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

use Doctrine\Common\Persistence\ObjectManager;

class B2bCustomerHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|FormInterface
     */
    protected $form;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ObjectManager
     */
    protected $manager;

    /**
     * @var B2bCustomerHandler
     */
    protected $handler;

    /**
     * @var B2bCustomer
     */
    protected $entity;

    /**
     * @var RequestChannelProvider|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestChannelProvider;

    protected function setUp()
    {
        $this->form = $this->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->getMock();

        $this->request = new Request();

        $this->manager = $this->getMockBuilder('Doctrine\Common\Persistence\ObjectManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->requestChannelProvider
            = $this->getMockBuilder('OroCRM\Bundle\ChannelBundle\Provider\RequestChannelProvider')
            ->disableOriginalConstructor()->getMock();

        $this->entity  = new B2bCustomer();
        $this->handler = new B2bCustomerHandler(
            $this->form,
            $this->request,
            $this->manager,
            $this->requestChannelProvider
        );
        $this->handler->setTagManager(
            $this->getMockBuilder('Oro\Bundle\TagBundle\Entity\TagManager')
                ->disableOriginalConstructor()->getMock()
        );
    }

    public function testProcessUnsupportedRequest()
    {
        $this->requestChannelProvider->expects($this->once())
            ->method('setDataChannel')
            ->with($this->entity);

        $this->form->expects($this->once())
            ->method('setData')
            ->with($this->entity);

        $this->form->expects($this->never())
            ->method('submit');

        $this->assertFalse($this->handler->process($this->entity));
    }

    /**
     * @dataProvider supportedMethods
     *
     * @param string $method
     */
    public function testProcessSupportedRequest($method)
    {
        $this->request->setMethod($method);

        $appendForm = $this->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->getMock();

        $appendForm->expects($this->once())
            ->method('getData')
            ->will($this->returnValue([]));

        $removeForm = $this->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->getMock();

        $removeForm->expects($this->any())
            ->method('getData')
            ->will($this->returnValue([]));

        $leadsForm = $this->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->getMock();

        $leadsForm->expects($this->at(0))
            ->method('get')
            ->with('added')
            ->will($this->returnValue($appendForm));

        $leadsForm->expects($this->at(1))
            ->method('get')
            ->with('removed')
            ->will($this->returnValue($removeForm));

        $appendForm2 = $this->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->getMock();

        $appendForm2->expects($this->once())
            ->method('getData')
            ->will($this->returnValue([]));

        $removeForm2 = $this->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->getMock();

        $removeForm2->expects($this->any())
            ->method('getData')
            ->will($this->returnValue([]));

        $opportunityForm = $this->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->getMock();

        $opportunityForm->expects($this->at(0))
            ->method('get')
            ->with('added')
            ->will($this->returnValue($appendForm2));

        $opportunityForm->expects($this->at(1))
            ->method('get')
            ->with('removed')
            ->will($this->returnValue($removeForm2));

        $this->form->expects($this->any())
            ->method('setData')
            ->with($this->entity);

        $this->form->expects($this->once())
            ->method('submit')
            ->with($this->request);

        $this->form->expects($this->exactly(2))
            ->method('has')
            ->will($this->returnValue(true));

        $this->form->expects($this->any())
            ->method('get')
            ->will(
                $this->returnValueMap(
                    [
                        ['leads', $leadsForm],
                        ['opportunities', $opportunityForm]
                    ]
                )
            );

        $this->assertFalse($this->handler->process($this->entity));
    }

    public function supportedMethods()
    {
        return [
            ['POST'],
            ['PUT']
        ];
    }

    public function testProcessWithoutLeadViewPermission()
    {
        $this->request->setMethod('POST');

        $this->form->expects($this->once())
            ->method('setData')
            ->with($this->entity);

        $this->form->expects($this->once())
            ->method('submit')
            ->with($this->request);

        $this->form->expects($this->once())
            ->method('isValid')
            ->will($this->returnValue(true));

        $this->form->expects($this->any())
            ->method('has')
            ->will(
                $this->returnValueMap(
                    [
                        ['leads', false],
                        ['opportunities', false]
                    ]
                )
            );

        $this->form->expects($this->never())
            ->method('get');

        $this->assertTrue($this->handler->process($this->entity));

        $actualLeads         = $this->entity->getLeads()->toArray();
        $actualOpportunities = $this->entity->getOpportunities()->toArray();
        $this->assertCount(0, $actualLeads);
        $this->assertEquals([], $actualLeads);
        $this->assertCount(0, $actualOpportunities);
        $this->assertEquals([], $actualOpportunities);
    }
}
