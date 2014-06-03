<?php

namespace OroCRM\Bundle\CampaignBundle\Tests\Unit\Form\Handler;

use Symfony\Component\HttpFoundation\Request;

use OroCRM\Bundle\CampaignBundle\Form\Handler\CampaignHandler;
use OroCRM\Bundle\CampaignBundle\Entity\Campaign;

class CampaignHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CampaignHandler
     */
    protected $handler;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $form;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $manager;

    /**
     * @var Campaign
     */
    protected $testEntity;

    protected function setUp()
    {
        $this->manager = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->form = $this->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->getMock();

        $this->request = new Request();

        $this->testEntity  = new Campaign();
        $this->handler = new CampaignHandler($this->form, $this->request, $this->manager);
        $this->form->expects($this->once())
            ->method('setData')
            ->with($this->testEntity);
    }

    public function testProcess()
    {
        $this->request->setMethod('POST');
        $this->form->expects($this->once())
            ->method('submit')
            ->with($this->request);
        $this->form->expects($this->once())
            ->method('isValid')
            ->will($this->returnValue(true));
        $this->manager->expects($this->once())
            ->method('persist')
            ->with($this->testEntity);
        $this->manager->expects($this->once())
            ->method('flush');

        $this->assertTrue($this->handler->process($this->testEntity));
    }

    public function testProcessWrongRequest()
    {
        $this->request->setMethod('GET');
        $this->assertFalse($this->handler->process($this->testEntity));
    }

    public function testWrongForm()
    {
        $this->request->setMethod('POST');
        $this->form->expects($this->once())
            ->method('submit')
            ->with($this->request);
        $this->form->expects($this->once())
            ->method('isValid')
            ->will($this->returnValue(false));
        $this->assertFalse($this->handler->process($this->testEntity));
    }
}
