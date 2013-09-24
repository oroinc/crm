<?php

namespace OroCRM\Bundle\AccountBundle\Tests\Unit\Form\Handler;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use OroCRM\Bundle\AccountBundle\Entity\Account;
use OroCRM\Bundle\ContactBundle\Entity\Contact;

use OroCRM\Bundle\AccountBundle\Form\Handler\AccountHandler;

class AccountHandlerTest extends \PHPUnit_Framework_TestCase
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
     * @var AccountHandler
     */
    protected $handler;

    /**
     * @var Account
     */
    protected $entity;

    protected function setUp()
    {
        $this->form = $this->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->getMock();

        $this->request = new Request();

        $this->manager = $this->getMockBuilder('Doctrine\Common\Persistence\ObjectManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->entity  = new Account();
        $this->handler = new AccountHandler($this->form, $this->request, $this->manager);
        $this->handler->setTagManager(
            $this->getMockBuilder('Oro\Bundle\TagBundle\Entity\TagManager')
                ->disableOriginalConstructor()
                ->getMock()
        );
    }


    public function testProcessUnsupportedRequest()
    {
        $this->form->expects($this->once())
            ->method('setData')
            ->with($this->entity);

        $this->form->expects($this->never())
            ->method('submit');

        $this->assertFalse($this->handler->process($this->entity));
    }

    /**
     * @dataProvider supportedMethods
     * @param string $method
     */
    public function testProcessSupportedRequest($method)
    {
        $this->request->setMethod($method);

        $contactsForm = $this->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->getMock();
        $appendForm = $this->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->getMock();
        $appendForm->expects($this->once())
            ->method('getData')
            ->will($this->returnValue(array()));
        $contactsForm->expects($this->at(0))
            ->method('get')
            ->with('added')
            ->will($this->returnValue($appendForm));

        $removeForm = $this->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->getMock();
        $removeForm->expects($this->once())
            ->method('getData')
            ->will($this->returnValue(array()));
        $contactsForm->expects($this->at(1))
            ->method('get')
            ->with('removed')
            ->will($this->returnValue($removeForm));

        $this->form->expects($this->once())
            ->method('setData')
            ->with($this->entity);

        $this->form->expects($this->once())
            ->method('submit')
            ->with($this->request);

        $this->form->expects($this->exactly(2))
            ->method('get')
            ->with('contacts')
            ->will($this->returnValue($contactsForm));

        $this->assertFalse($this->handler->process($this->entity));
    }

    public function supportedMethods()
    {
        return array(
            array('POST'),
            array('PUT')
        );
    }

    public function testProcessValidData()
    {
        $appendedContact = new Contact();
        $appendedContact->setId(1);

        $removedContact = new Contact();
        $removedContact->setId(2);

        $this->entity->addContact($removedContact);

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

        $contactsForm = $this->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->getMock();
        $appendForm = $this->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->getMock();
        $appendForm->expects($this->once())
            ->method('getData')
            ->will($this->returnValue(array($appendedContact)));
        $contactsForm->expects($this->at(0))
            ->method('get')
            ->with('added')
            ->will($this->returnValue($appendForm));

        $removeForm = $this->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->getMock();
        $removeForm->expects($this->once())
            ->method('getData')
            ->will($this->returnValue(array($removedContact)));
        $contactsForm->expects($this->at(1))
            ->method('get')
            ->with('removed')
            ->will($this->returnValue($removeForm));

        $this->form->expects($this->exactly(2))
            ->method('get')
            ->with('contacts')
            ->will($this->returnValue($contactsForm));

        $this->manager->expects($this->once())
            ->method('persist')
            ->with($this->entity);

        $this->manager->expects($this->once())
            ->method('flush');

        $this->assertTrue($this->handler->process($this->entity));

        $actualContacts = $this->entity->getContacts()->toArray();
        $this->assertCount(1, $actualContacts);
        $this->assertEquals($appendedContact, current($actualContacts));
    }
}
