<?php

namespace Oro\Bundle\AccountBundle\Tests\Unit\Form\Handler;

use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\AccountBundle\Form\Handler\AccountHandler;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class AccountHandlerTest extends \PHPUnit\Framework\TestCase
{
    private const FORM_DATA = ['field' => 'value'];

    /** @var FormInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $form;

    /** @var Request */
    private $request;

    /** @var ObjectManager|\PHPUnit\Framework\MockObject\MockObject */
    private $manager;

    /** @var Account */
    private $entity;

    /** @var AccountHandler */
    private $handler;

    protected function setUp(): void
    {
        $this->form = $this->createMock(Form::class);
        $this->request = new Request();
        $this->manager = $this->createMock(ObjectManager::class);
        $this->entity = new Account();

        $this->handler = new AccountHandler($this->manager);
    }

    public function testProcessUnsupportedRequest(): void
    {
        $this->form->expects($this->once())
            ->method('setData')
            ->with($this->entity);

        $this->form->expects($this->never())
            ->method('submit');

        $this->assertFalse($this->handler->process($this->entity, $this->form, $this->request));
    }

    /**
     * @dataProvider supportedMethods
     */
    public function testProcessSupportedRequest(string $method): void
    {
        $this->request->initialize([], self::FORM_DATA);
        $this->request->setMethod($method);

        $this->form->expects($this->once())
            ->method('setData')
            ->with($this->entity);

        $this->form->expects($this->once())
            ->method('submit')
            ->with(self::FORM_DATA);

        $this->assertFalse($this->handler->process($this->entity, $this->form, $this->request));
    }

    public function supportedMethods(): array
    {
        return [
            ['POST'],
            ['PUT'],
        ];
    }

    public function testProcessValidData(): void
    {
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
            ->willReturn(true);

        $this->manager->expects($this->once())
            ->method('persist')
            ->with($this->entity);

        $this->manager->expects($this->once())
            ->method('flush');

        $this->assertTrue($this->handler->process($this->entity, $this->form, $this->request));
    }

    public function testProcessWithoutContactViewPermission(): void
    {
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
            ->willReturn(true);

        $this->form->expects($this->never())
            ->method('get');

        $this->assertTrue($this->handler->process($this->entity, $this->form, $this->request));
    }
}
