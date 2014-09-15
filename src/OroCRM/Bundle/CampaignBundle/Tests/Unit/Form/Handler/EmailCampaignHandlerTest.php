<?php

namespace OroCRM\Bundle\CampaignBundle\Tests\Unit\Form\Handler;

use OroCRM\Bundle\CampaignBundle\Form\Handler\EmailCampaignHandler;
use Symfony\Component\HttpFoundation\Request;

class EmailCampaignHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $form;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $registry;

    /**
     * @var EmailCampaignHandler
     */
    protected $handler;

    protected function setUp()
    {
        $this->request = new Request();
        $this->form = $this->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->getMock();
        $this->registry = $this->getMockBuilder('Symfony\Bridge\Doctrine\RegistryInterface')
            ->getMockForAbstractClass();

        $this->handler = new EmailCampaignHandler($this->request, $this->form, $this->registry);
    }

    public function testProcessGet()
    {
        $data = $this->getMockBuilder('OroCRM\Bundle\CampaignBundle\Entity\EmailCampaign')
            ->disableOriginalConstructor()
            ->getMock();

        $this->form->expects($this->once())
            ->method('setData')
            ->with($data);

        $this->request->setMethod('GET');
        $this->form->expects($this->never())
            ->method('submit');

        $this->assertFalse($this->handler->process($data));
    }

    public function testProcessUpdateInvalid()
    {
        $data = $this->getMockBuilder('OroCRM\Bundle\CampaignBundle\Entity\EmailCampaign')
            ->disableOriginalConstructor()
            ->getMock();

        $this->form->expects($this->once())
            ->method('setData')
            ->with($data);

        $this->request->setMethod('POST');
        $this->form->expects($this->once())
            ->method('submit')
            ->with($this->request);
        $this->registry->expects($this->never())
            ->method($this->anything());

        $this->form->expects($this->once())
            ->method('isValid')
            ->will($this->returnValue(false));

        $this->assertFalse($this->handler->process($data));
    }

    public function testProcessUpdateMarker()
    {
        $data = $this->getMockBuilder('OroCRM\Bundle\CampaignBundle\Entity\EmailCampaign')
            ->disableOriginalConstructor()
            ->getMock();

        $this->form->expects($this->once())
            ->method('setData')
            ->with($data);

        $this->request->setMethod('PUT');
        $this->form->expects($this->once())
            ->method('submit')
            ->with($this->request);
        $this->registry->expects($this->never())
            ->method($this->anything());

        $this->request->request->set(EmailCampaignHandler::UPDATE_MARKER, true);
        $this->form->expects($this->never())
            ->method('isValid');

        $this->assertFalse($this->handler->process($data));
    }

    public function testProcess()
    {
        $data = $this->getMockBuilder('OroCRM\Bundle\CampaignBundle\Entity\EmailCampaign')
            ->disableOriginalConstructor()
            ->getMock();

        $this->form->expects($this->once())
            ->method('setData')
            ->with($data);

        $this->request->setMethod('POST');
        $this->form->expects($this->once())
            ->method('submit')
            ->with($this->request);

        $manager = $this->getMockBuilder('\Doctrine\Common\Persistence\ObjectManager')
            ->disableOriginalConstructor()
            ->getMock();
        $manager->expects($this->once())
            ->method('persist')
            ->with($data);
        $manager->expects($this->once())
            ->method('flush');
        $this->registry->expects($this->once())
            ->method('getManagerForClass')
            ->with('OroCRMCampaignBundle:EmailCampaign')
            ->will($this->returnValue($manager));

        $this->form->expects($this->once())
            ->method('isValid')
            ->will($this->returnValue(true));

        $this->assertTrue($this->handler->process($data));
    }
}
