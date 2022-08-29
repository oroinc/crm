<?php

namespace Oro\Bundle\ContactBundle\Tests\Unit\Form\Handler;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\UnitOfWork;
use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\ContactBundle\Form\Handler\ContactHandler;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class ContactHandlerTest extends \PHPUnit\Framework\TestCase
{
    private const FORM_DATA = ['field' => 'value'];

    /** @var FormInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $form;

    /** @var Request */
    private $request;

    /** @var EntityManagerInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $manager;

    /** @var Contact */
    private $entity;

    /** @var ContactHandler */
    private $handler;

    protected function setUp(): void
    {
        $this->form = $this->createMock(Form::class);
        $this->request = new Request();
        $this->manager = $this->createMock(EntityManagerInterface::class);
        $this->entity = new Contact();

        $this->handler = new ContactHandler($this->manager);
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
        $this->form->expects($this->once())
            ->method('setData')
            ->with($this->entity);

        $this->request->initialize([], self::FORM_DATA);
        $this->request->setMethod($method);

        $this->form->expects($this->once())
            ->method('submit')
            ->with(self::FORM_DATA);

        $this->assertFalse($this->handler->process($this->entity, $this->form, $this->request));
    }

    public function supportedMethods(): array
    {
        return [
            ['POST'],
            ['PUT']
        ];
    }

    /**
     * @dataProvider processValidDataProvider
     */
    public function testProcessValidData(bool $isDataChanged): void
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
            ->willReturn(true);

        $appendForm = $this->createMock(Form::class);
        $appendForm->expects($this->once())
            ->method('getData')
            ->willReturn([$appendedAccount]);

        $removeForm = $this->createMock(Form::class);
        $removeForm->expects($this->once())
            ->method('getData')
            ->willReturn([$removedAccount]);

        $this->form->expects($this->exactly(2))
            ->method('get')
            ->willReturnMap([
                ['appendAccounts', $appendForm],
                ['removeAccounts', $removeForm]
            ]);

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

        $this->assertTrue($this->handler->process($this->entity, $this->form, $this->request));

        $actualAccounts = $this->entity->getAccounts()->toArray();
        $this->assertCount(1, $actualAccounts);
        $this->assertEquals($appendedAccount, current($actualAccounts));
    }

    public function processValidDataProvider(): array
    {
        return [
            [true],
            [false],
        ];
    }

    private function configureUnitOfWork(bool $isChangesExists): void
    {
        $uow = $this->createMock(UnitOfWork::class);
        $uow->expects($this->once())
            ->method('computeChangeSets');
        $uow->expects($this->once())
            ->method('getEntityChangeSet')
            ->with($this->entity)
            ->willReturn($isChangesExists ? [1] : []);
        $uow->expects($this->once())
            ->method('getScheduledEntityUpdates')
            ->willReturn([1]);

        $this->manager->expects($this->once())
            ->method('getUnitOfWork')
            ->willReturn($uow);
    }
}
