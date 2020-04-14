<?php

namespace Oro\Bundle\ContactBundle\Tests\Unit\Form\Handler;

use Doctrine\ORM\EntityManagerInterface;
use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\ContactBundle\Form\Handler\ContactHandler;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class ContactHandlerTest extends \PHPUnit\Framework\TestCase
{
    const FORM_DATA = ['field' => 'value'];

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|FormInterface
     */
    protected $form;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|EntityManagerInterface
     */
    protected $manager;

    /**
     * @var ContactHandler
     */
    protected $handler;

    /**
     * @var Contact
     */
    protected $entity;

    protected function setUp(): void
    {
        $this->form = $this->createMock(Form::class);
        $this->request = new Request();
        $requestStack = new RequestStack();
        $requestStack->push($this->request);
        $this->manager = $this->createMock(EntityManagerInterface::class);

        $this->entity = new Contact();
        $this->handler = new ContactHandler($this->form, $requestStack, $this->manager);
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
        $this->form->expects($this->once())
            ->method('setData')
            ->with($this->entity);

        $this->request->initialize([], self::FORM_DATA);
        $this->request->setMethod($method);

        $this->form->expects($this->once())
            ->method('submit')
            ->with(self::FORM_DATA);

        $this->assertFalse($this->handler->process($this->entity));
    }

    public function supportedMethods()
    {
        return array(
            array('POST'),
            array('PUT')
        );
    }

    /**
     * @dataProvider processValidDataProvider
     *
     * @param bool $isDataChanged
     */
    public function testProcessValidData($isDataChanged)
    {
        $appendedAccount = new Account();
        $appendedAccount->setId(1);

        $removedAccount = new Account();
        $removedAccount->setId(2);

        $this->entity->addAccount($removedAccount);

        $this->request->initialize([], self::FORM_DATA);
        $this->request->setMethod('POST');

        $this->form->expects($this->once())
            ->method('setData')
            ->with($this->entity);

        $this->form->expects($this->once())
            ->method('submit')
            ->with(self::FORM_DATA);

        $this->form->expects($this->once())
            ->method('isValid')
            ->will($this->returnValue(true));

        $appendForm = $this->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->getMock();
        $appendForm->expects($this->once())
            ->method('getData')
            ->will($this->returnValue(array($appendedAccount)));
        $this->form->expects($this->at(5))
            ->method('get')
            ->with('appendAccounts')
            ->will($this->returnValue($appendForm));

        $removeForm = $this->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->getMock();
        $removeForm->expects($this->once())
            ->method('getData')
            ->will($this->returnValue(array($removedAccount)));
        $this->form->expects($this->at(6))
            ->method('get')
            ->with('removeAccounts')
            ->will($this->returnValue($removeForm));

        if ($isDataChanged) {
            $this->manager->expects($this->once())
                ->method('persist')
                ->with($this->entity);
        } else {
            $this->manager->expects($this->exactly(2))
                ->method('persist')
                ->with($this->entity);
        }

        $this->manager->expects($this->once())
            ->method('flush');

        $this->configureUnitOfWork($isDataChanged);

        $this->assertTrue($this->handler->process($this->entity));

        $actualAccounts = $this->entity->getAccounts()->toArray();
        $this->assertCount(1, $actualAccounts);
        $this->assertEquals($appendedAccount, current($actualAccounts));
    }

    public function processValidDataProvider()
    {
        return [
            [true],
            [false],
        ];
    }

    /**
     * @param bool $isChangesExists
     */
    protected function configureUnitOfWork($isChangesExists)
    {
        $uowMock = $this->getMockBuilder('\Doctrine\ORM\UnitOfWork')
            ->disableOriginalConstructor()
            ->getMock();

        $this->manager->expects($this->once())
            ->method('getUnitOfWork')
            ->will($this->returnValue($uowMock));

        $uowMock->expects($this->once())
            ->method('computeChangeSets');

        $uowMock->expects($this->once())
            ->method('getEntityChangeSet')
            ->with($this->entity)
            ->will($this->returnValue($isChangesExists ? [1] : []));

        $uowMock->expects($this->once())
            ->method('getScheduledEntityUpdates')
            ->will($this->returnValue([1]));
    }
}
