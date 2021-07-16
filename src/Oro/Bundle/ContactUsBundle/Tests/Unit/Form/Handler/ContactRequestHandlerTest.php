<?php

namespace Oro\Bundle\ContactUsBundle\Tests\Unit\Form\Handler;

use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\ContactUsBundle\Entity\ContactRequest;
use Oro\Bundle\ContactUsBundle\Form\Handler\ContactRequestHandler;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class ContactRequestHandlerTest extends \PHPUnit\Framework\TestCase
{
    const FORM_DATA = ['field' => 'value'];

    /** @var FormInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $form;

    /** @var Request */
    protected $request;

    /** @var \PHPUnit\Framework\MockObject\MockObject|EntityManager */
    protected $em;

    /** @var ContactRequestHandler */
    protected $handler;

    /** @var ContactRequest */
    protected $entity;

    protected function setUp(): void
    {
        $this->form = $this->createMock(Form::class);
        $this->request = new Request();
        $requestStack = new RequestStack();
        $requestStack->push($this->request);
        $this->em = $this->createMock(EntityManager::class);

        $registry = $this->createMock(ManagerRegistry::class);
        $registry->expects($this->any())
            ->method('getManagerForClass')
            ->with(ContactRequest::class)
            ->willReturn($this->em);

        $this->entity  = new ContactRequest();
        $this->handler = new ContactRequestHandler($this->form, $requestStack, $registry);
    }

    protected function tearDown(): void
    {
        unset($this->form, $this->request, $this->em, $this->handler, $this->entity);
    }

    public function testProcessUnsupportedRequest()
    {
        $this->form->expects($this->once())->method('setData')
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
        $this->form->expects($this->once())->method('setData')
            ->with($this->entity);

        $this->request->initialize([], self::FORM_DATA);
        $this->request->setMethod($method);

        $this->form->expects($this->once())->method('submit')
            ->with(self::FORM_DATA);

        $this->assertFalse($this->handler->process($this->entity));
    }

    /**
     * @return array
     */
    public function supportedMethods()
    {
        return [
            ['POST'],
            ['PUT']
        ];
    }

    public function testProcessValidData()
    {
        $this->form->expects($this->once())->method('setData')
            ->with($this->entity);

        $this->request->initialize([], self::FORM_DATA);
        $this->request->setMethod('POST');

        $this->form->expects($this->once())->method('submit')
            ->with(self::FORM_DATA);

        $this->form->expects($this->once())->method('isValid')
            ->will($this->returnValue(true));

        $this->em->expects($this->once())->method('persist')
            ->with($this->entity);

        $this->em->expects($this->once())->method('flush');

        $this->assertTrue($this->handler->process($this->entity));
    }
}
