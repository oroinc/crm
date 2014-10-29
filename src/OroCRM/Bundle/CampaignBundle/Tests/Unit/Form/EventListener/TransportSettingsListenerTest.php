<?php

namespace OroCRM\Bundle\CampaignBundle\Tests\Unit\Form\EventListener;

use Symfony\Component\Form\FormEvents;
use OroCRM\Bundle\CampaignBundle\Form\EventListener\TransportSettingsListener;

class TransportSettingsListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $emailTransportProvider;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $doctrineHelper;

    /**
     * @var TransportSettingsListener
     */
    protected $listener;

    protected function setUp()
    {
        $this->emailTransportProvider = $this
            ->getMockBuilder('OroCRM\Bundle\CampaignBundle\Provider\EmailTransportProvider')
            ->disableOriginalConstructor()
            ->getMock();
        $this->doctrineHelper = $this->getMockBuilder('Oro\Bundle\EntityBundle\ORM\DoctrineHelper')
            ->disableOriginalConstructor()
            ->getMock();

        $this->listener = new TransportSettingsListener($this->emailTransportProvider, $this->doctrineHelper);
    }

    public function testGetSubscribedEvents()
    {
        $expected = [
            FormEvents::PRE_SET_DATA  => 'preSet',
            FormEvents::POST_SET_DATA => 'postSet',
            FormEvents::PRE_SUBMIT    => 'preSubmit'
        ];
        $this->assertEquals($expected, $this->listener->getSubscribedEvents());
    }

    public function testPreSetNoData()
    {
        $event = $this->getMockBuilder('Symfony\Component\Form\FormEvent')
            ->disableOriginalConstructor()
            ->getMock();
        $event->expects($this->never())
            ->method('getForm');
        $this->emailTransportProvider->expects($this->never())
            ->method($this->anything());
        $this->listener->preSet($event);
    }

    public function testPreSetHasTransportHasForm()
    {
        $transportName = 'internal';
        $entity = $this->getMockBuilder('OroCRM\Bundle\CampaignBundle\Entity\EmailCampaign')
            ->disableOriginalConstructor()
            ->getMock();
        $entity->expects($this->once())
            ->method('getTransport')
            ->will($this->returnValue($transportName));
        $entity->expects($this->once())
            ->method('setTransport')
            ->with($transportName);
        $transport = $this->getMockBuilder('OroCRM\Bundle\CampaignBundle\Transport\TransportInterface')
            ->getMock();
        $transport->expects($this->any())
            ->method('getName')
            ->will($this->returnValue($transportName));
        $transport->expects($this->once())
            ->method('getSettingsFormType')
            ->will($this->returnValue('test_type'));
        $form = $this->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->getMock();
        $form->expects($this->once())
            ->method('add')
            ->with('transportSettings', 'test_type', ['required' => true]);

        $event = $this->getMockBuilder('Symfony\Component\Form\FormEvent')
            ->disableOriginalConstructor()
            ->getMock();
        $event->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($entity));
        $event->expects($this->once())
            ->method('getForm')
            ->will($this->returnValue($form));
        $event->expects($this->once())
            ->method('setData')
            ->with($entity);
        $this->emailTransportProvider->expects($this->once())
            ->method('getTransportByName')
            ->with($transportName)
            ->will($this->returnValue($transport));

        $this->listener->preSet($event);
    }

    public function testPreSetHasTransportNoFormHadBefore()
    {
        $transportName = 'internal';
        $entity = $this->getMockBuilder('OroCRM\Bundle\CampaignBundle\Entity\EmailCampaign')
            ->disableOriginalConstructor()
            ->getMock();
        $entity->expects($this->once())
            ->method('getTransport')
            ->will($this->returnValue($transportName));
        $entity->expects($this->once())
            ->method('setTransport')
            ->with($transportName);
        $transport = $this->getMockBuilder('OroCRM\Bundle\CampaignBundle\Transport\TransportInterface')
            ->getMock();
        $transport->expects($this->any())
            ->method('getName')
            ->will($this->returnValue($transportName));
        $transport->expects($this->once())
            ->method('getSettingsFormType')
            ->will($this->returnValue(null));
        $form = $this->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->getMock();
        $form->expects($this->once())
            ->method('has')
            ->with('transportSettings')
            ->will($this->returnValue(true));
        $form->expects($this->once())
            ->method('remove')
            ->with('transportSettings');

        $event = $this->getMockBuilder('Symfony\Component\Form\FormEvent')
            ->disableOriginalConstructor()
            ->getMock();
        $event->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($entity));
        $event->expects($this->once())
            ->method('getForm')
            ->will($this->returnValue($form));
        $event->expects($this->once())
            ->method('setData')
            ->with($entity);
        $this->emailTransportProvider->expects($this->once())
            ->method('getTransportByName')
            ->with($transportName)
            ->will($this->returnValue($transport));

        $this->listener->preSet($event);
    }

    public function testPreSetHasTransportNoForm()
    {
        $transportName = 'internal';
        $entity = $this->getMockBuilder('OroCRM\Bundle\CampaignBundle\Entity\EmailCampaign')
            ->disableOriginalConstructor()
            ->getMock();
        $entity->expects($this->once())
            ->method('getTransport')
            ->will($this->returnValue($transportName));
        $entity->expects($this->once())
            ->method('setTransport')
            ->with($transportName);
        $transport = $this->getMockBuilder('OroCRM\Bundle\CampaignBundle\Transport\TransportInterface')
            ->getMock();
        $transport->expects($this->any())
            ->method('getName')
            ->will($this->returnValue($transportName));
        $transport->expects($this->once())
            ->method('getSettingsFormType')
            ->will($this->returnValue(null));
        $form = $this->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->getMock();
        $form->expects($this->once())
            ->method('has')
            ->with('transportSettings')
            ->will($this->returnValue(false));
        $form->expects($this->never())
            ->method('remove');

        $event = $this->getMockBuilder('Symfony\Component\Form\FormEvent')
            ->disableOriginalConstructor()
            ->getMock();
        $event->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($entity));
        $event->expects($this->once())
            ->method('getForm')
            ->will($this->returnValue($form));
        $event->expects($this->once())
            ->method('setData')
            ->with($entity);
        $this->emailTransportProvider->expects($this->once())
            ->method('getTransportByName')
            ->with($transportName)
            ->will($this->returnValue($transport));

        $this->listener->preSet($event);
    }

    public function testPreSetNoTransportSetHasForm()
    {
        $transportName = 'internal';
        $entity = $this->getMockBuilder('OroCRM\Bundle\CampaignBundle\Entity\EmailCampaign')
            ->disableOriginalConstructor()
            ->getMock();
        $entity->expects($this->once())
            ->method('getTransport')
            ->will($this->returnValue(null));
        $entity->expects($this->once())
            ->method('setTransport')
            ->with($transportName);
        $transport = $this->getMockBuilder('OroCRM\Bundle\CampaignBundle\Transport\TransportInterface')
            ->getMock();
        $transport->expects($this->any())
            ->method('getName')
            ->will($this->returnValue($transportName));
        $transport->expects($this->once())
            ->method('getSettingsFormType')
            ->will($this->returnValue('test_type'));
        $form = $this->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->getMock();
        $form->expects($this->once())
            ->method('add')
            ->with('transportSettings', 'test_type', ['required' => true]);

        $event = $this->getMockBuilder('Symfony\Component\Form\FormEvent')
            ->disableOriginalConstructor()
            ->getMock();
        $event->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($entity));
        $event->expects($this->once())
            ->method('getForm')
            ->will($this->returnValue($form));
        $event->expects($this->once())
            ->method('setData')
            ->with($entity);
        $this->emailTransportProvider->expects($this->once())
            ->method('getTransports')
            ->will($this->returnValue([$transport]));

        $this->listener->preSet($event);
    }

    public function testPostSetNoData()
    {
        $event = $this->getMockBuilder('Symfony\Component\Form\FormEvent')
            ->disableOriginalConstructor()
            ->getMock();
        $event->expects($this->never())
            ->method('getForm');
        $this->emailTransportProvider->expects($this->never())
            ->method($this->anything());
        $this->listener->postSet($event);
    }

    public function testPostSet()
    {
        $transportName = 'internal';
        $entity = $this->getMockBuilder('OroCRM\Bundle\CampaignBundle\Entity\EmailCampaign')
            ->disableOriginalConstructor()
            ->getMock();
        $entity->expects($this->once())
            ->method('getTransport')
            ->will($this->returnValue($transportName));

        $transportSubform = $this->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->getMock();
        $transportSubform->expects($this->once())
            ->method('setData')
            ->with($transportName);

        $form = $this->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->getMock();
        $form->expects($this->once())
            ->method('get')
            ->with('transport')
            ->will($this->returnValue($transportSubform));

        $event = $this->getMockBuilder('Symfony\Component\Form\FormEvent')
            ->disableOriginalConstructor()
            ->getMock();
        $event->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($entity));
        $event->expects($this->once())
            ->method('getForm')
            ->will($this->returnValue($form));
        $this->emailTransportProvider->expects($this->never())
            ->method($this->anything());
        $this->listener->postSet($event);
    }

    public function testPreSubmitTransport()
    {
        $transportName = 'internal';

        $entity = $this->getMockBuilder('OroCRM\Bundle\CampaignBundle\Entity\EmailCampaign')
            ->disableOriginalConstructor()
            ->getMock();
        $entity->expects($this->any())
            ->method('getTransport')
            ->will($this->returnValue('other'));
        $entity->expects($this->once())
            ->method('setTransport')
            ->will($this->returnValue($transportName));

        $data = ['transport' => $transportName];

        $transportSubform = $this->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->getMock();
        $transportSubform->expects($this->once())
            ->method('setData')
            ->with($transportName);

        $form = $this->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->getMock();
        $form->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($entity));
        $form->expects($this->any())
            ->method('has')
            ->with('transportSettings')
            ->will($this->returnValue(true));
        $form->expects($this->once())
            ->method('add')
            ->with('transportSettings', 'test_type', ['required' => true]);
        $form->expects($this->once())
            ->method('get')
            ->with('transport')
            ->will($this->returnValue($transportSubform));

        $event = $this->getMockBuilder('Symfony\Component\Form\FormEvent')
            ->disableOriginalConstructor()
            ->getMock();
        $event->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));
        $event->expects($this->once())
            ->method('getForm')
            ->will($this->returnValue($form));

        $expectedSetData = $data;
        $expectedSetData['transportSettings']['parentData'] = $data;
        $event->expects($this->once())
            ->method('setData')
            ->with($expectedSetData);

        $transport = $this->getMockBuilder('OroCRM\Bundle\CampaignBundle\Transport\TransportInterface')
            ->getMock();
        $transport->expects($this->any())
            ->method('getName')
            ->will($this->returnValue($transportName));
        $transport->expects($this->once())
            ->method('getSettingsFormType')
            ->will($this->returnValue('test_type'));
        $transport->expects($this->once())
            ->method('getSettingsEntityFQCN')
            ->will($this->returnValue('\stdClass'));

        $this->emailTransportProvider->expects($this->once())
            ->method('getTransportByName')
            ->with($transportName)
            ->will($this->returnValue($transport));

        $this->doctrineHelper->expects($this->once())
            ->method('createEntityInstance')
            ->with('\stdClass')
            ->will($this->returnValue(new \stdClass()));

        $this->listener->preSubmit($event);
    }

    public function testPreSubmitTransportSameTransport()
    {
        $transportName = 'internal';

        $entity = $this->getMockBuilder('OroCRM\Bundle\CampaignBundle\Entity\EmailCampaign')
            ->disableOriginalConstructor()
            ->getMock();
        $entity->expects($this->any())
            ->method('getTransport')
            ->will($this->returnValue($transportName));
        $entity->expects($this->once())
            ->method('setTransport')
            ->will($this->returnValue($transportName));

        $data = ['transport' => $transportName];

        $transportSubform = $this->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->getMock();
        $transportSubform->expects($this->once())
            ->method('setData')
            ->with($transportName);

        $form = $this->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->getMock();
        $form->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($entity));
        $form->expects($this->any())
            ->method('has')
            ->with('transportSettings')
            ->will($this->returnValue(true));
        $form->expects($this->once())
            ->method('add')
            ->with('transportSettings', 'test_type', ['required' => true]);
        $form->expects($this->once())
            ->method('get')
            ->with('transport')
            ->will($this->returnValue($transportSubform));

        $event = $this->getMockBuilder('Symfony\Component\Form\FormEvent')
            ->disableOriginalConstructor()
            ->getMock();
        $event->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));
        $event->expects($this->once())
            ->method('getForm')
            ->will($this->returnValue($form));

        $expectedSetData = $data;
        $expectedSetData['transportSettings']['parentData'] = $data;
        $event->expects($this->once())
            ->method('setData')
            ->with($expectedSetData);

        $transport = $this->getMockBuilder('OroCRM\Bundle\CampaignBundle\Transport\TransportInterface')
            ->getMock();
        $transport->expects($this->any())
            ->method('getName')
            ->will($this->returnValue($transportName));
        $transport->expects($this->once())
            ->method('getSettingsFormType')
            ->will($this->returnValue('test_type'));
        $transport->expects($this->never())
            ->method('getSettingsEntityFQCN');

        $this->emailTransportProvider->expects($this->once())
            ->method('getTransportByName')
            ->with($transportName)
            ->will($this->returnValue($transport));

        $this->doctrineHelper->expects($this->never())
            ->method('createEntityInstance');

        $this->listener->preSubmit($event);
    }
}
