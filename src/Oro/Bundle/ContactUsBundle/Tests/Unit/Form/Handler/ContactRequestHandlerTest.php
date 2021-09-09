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

    private FormInterface|\PHPUnit\Framework\MockObject\MockObject $form;

    private Request $request;

    private \PHPUnit\Framework\MockObject\MockObject|EntityManager $em;

    private ContactRequestHandler $handler;

    private ContactRequest $entity;

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

    public function testProcessUnsupportedRequest(): void
    {
        $this->form->expects($this->once())->method('setData')
            ->with($this->entity);

        $this->form->expects($this->never())
            ->method('submit');

        self::assertFalse($this->handler->process($this->entity));
    }

    /**
     * @dataProvider supportedMethods
     */
    public function testProcessSupportedRequest(string $method): void
    {
        $this->form->expects($this->once())->method('setData')
            ->with($this->entity);

        $this->request->initialize([], self::FORM_DATA);
        $this->request->setMethod($method);

        $this->form->expects($this->once())->method('submit')
            ->with(self::FORM_DATA);

        self::assertFalse($this->handler->process($this->entity));
    }

    public function supportedMethods(): array
    {
        return [
            ['POST'],
            ['PUT']
        ];
    }

    public function testProcessValidData(): void
    {
        $this->form->expects($this->once())->method('setData')
            ->with($this->entity);

        $this->request->initialize([], self::FORM_DATA);
        $this->request->setMethod('POST');

        $this->form->expects($this->once())->method('submit')
            ->with(self::FORM_DATA);

        $this->form->expects($this->once())->method('isValid')
            ->willReturn(true);

        $this->em->expects($this->once())->method('persist')
            ->with($this->entity);

        $this->em->expects($this->once())->method('flush');

        self::assertTrue($this->handler->process($this->entity));
    }
}
