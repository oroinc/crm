<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Form\Handler;

use Oro\Bundle\FormBundle\Tests\Unit\Model\UpdateHandlerTest;
use Oro\Bundle\MagentoBundle\Entity\Customer;
use Oro\Bundle\MagentoBundle\Form\Handler\CustomerHandler;
use Oro\Bundle\MagentoBundle\Service\CustomerStateHandler;
use Oro\Bundle\MagentoBundle\Service\StateManager;
use Symfony\Component\Form\Form;

class CustomerHandlerTest extends UpdateHandlerTest
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->handler = new CustomerHandler(
            $this->requestStack,
            $this->session,
            $this->router,
            $this->doctrineHelper,
            $this->formHandler
        );
        $this->handler->setStateHandler(new CustomerStateHandler(new StateManager($this->doctrineHelper)));
    }

    public function testHandleUpdateWorksWithValidForm()
    {
        $entity = $this->getObject();

        $this->request->initialize(['_wid' => 'WID'], self::FORM_DATA);
        $this->request->setMethod('POST');
        $this->form->expects($this->once())
            ->method('submit')
            ->with(self::FORM_DATA);
        $this->form->expects($this->once())
            ->method('isValid')
            ->will($this->returnValue(true));
        $em = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();
        $em->expects($this->atLeastOnce())
            ->method('persist');

        $em->expects($this->atLeastOnce())
            ->method('flush');
        $this->doctrineHelper->expects($this->atLeastOnce())
            ->method('getEntityManager')
            ->with($entity)
            ->will($this->returnValue($em));
        $this->doctrineHelper->expects($this->once())
            ->method('getSingleEntityIdentifier')
            ->with($entity)
            ->will($this->returnValue(1));

        $expected = $this->getExpectedSaveData($this->form, $entity);
        $expected['savedId'] = 1;

        $result = $this->handler->handleUpdate(
            $entity,
            $this->form,
            ['route' => 'test_update'],
            ['route' => 'test_view'],
            'Saved'
        );
        $this->assertEquals($expected, $result);
    }

    public function testUpdateWithInvalidForm()
    {
        $data = $this->getObject();
        $this->form->expects($this->once())
            ->method('setData')
            ->with($data);
        $this->request->initialize(['_wid' => 'WID'], self::FORM_DATA);
        $this->request->setMethod('POST');
        $this->form->expects($this->once())
            ->method('submit')
            ->with(self::FORM_DATA);
        $this->form->expects($this->once())
            ->method('isValid')
            ->will($this->returnValue(false));

        $expected = $this->getExpectedSaveData($this->form, $data);

        $result = $this->handler->update($data, $this->form, 'Saved');
        $this->assertEquals($expected, $result);
    }

    public function testUpdateWorksWithValidForm()
    {
        $data = $this->getObject();
        $this->form->expects($this->once())
            ->method('setData')
            ->with($data);
        $this->request->initialize(['_wid' => 'WID'], self::FORM_DATA);
        $this->request->setMethod('POST');
        $this->form->expects($this->once())
            ->method('submit')
            ->with(self::FORM_DATA);
        $this->form->expects($this->once())
            ->method('isValid')
            ->will($this->returnValue(true));

        $em = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();
        $em->expects($this->exactly(2))
            ->method('persist')
            ->with($data);
        $em->expects($this->exactly(2))
            ->method('flush');
        $this->doctrineHelper->expects($this->exactly(2))
            ->method('getEntityManager')
            ->with($data)
            ->will($this->returnValue($em));
        $this->doctrineHelper->expects($this->once())
            ->method('getSingleEntityIdentifier')
            ->with($data)
            ->will($this->returnValue(1));

        $expected = $this->getExpectedSaveData($this->form, $data);
        $expected['savedId'] = 1;

        $result = $this->handler->update($data, $this->form, 'Saved');
        $this->assertEquals($expected, $result);
    }

    public function testHandleUpdateWorksWithInvalidForm()
    {
        /** @var \PHPUnit\Framework\MockObject\MockObject|Form $form */
        $form = $this->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->getMock();
        $entity = $this->getObject();
        $form->expects($this->once())
            ->method('setData')
            ->with($entity);
        $this->request->initialize(['_wid' => 'WID'], self::FORM_DATA);
        $this->request->setMethod('POST');
        $form->expects($this->once())
            ->method('submit')
            ->with(self::FORM_DATA);
        $form->expects($this->once())
            ->method('isValid')
            ->will($this->returnValue(false));

        $expected = $this->getExpectedSaveData($form, $entity);

        $result = $this->handler->handleUpdate(
            $entity,
            $form,
            ['route' => 'test_update'],
            ['route' => 'test_view'],
            'Saved'
        );
        $this->assertEquals($expected, $result);
    }

    public function testHandleUpdateWorksWhenFormFlushFailed()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Test flush exception');

        $entity = $this->getObject();
        $this->form->expects($this->once())
            ->method('setData')
            ->with($entity);
        $this->request->initialize([], self::FORM_DATA);
        $this->request->setMethod('POST');
        $this->form->expects($this->once())
            ->method('submit')
            ->with(self::FORM_DATA);
        $this->form->expects($this->once())
            ->method('isValid')
            ->will($this->returnValue(true));

        $em = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();
        $em->expects($this->once())
            ->method('persist')
            ->with($entity);
        $em->expects($this->once())
            ->method('flush')
            ->willThrowException(new \Exception('Test flush exception'));
        $this->doctrineHelper->expects($this->once())
            ->method('getEntityManager')
            ->with($entity)
            ->will($this->returnValue($em));

        $this->handler->handleUpdate(
            $entity,
            $this->form,
            ['route' => 'test_update'],
            ['route' => 'test_view'],
            'Saved'
        );
    }

    public function testUpdateWorksWhenFormFlushFailed()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Test flush exception');

        $data = $this->getObject();
        $this->form->expects($this->once())
            ->method('setData')
            ->with($data);
        $this->request->initialize([], self::FORM_DATA);
        $this->request->setMethod('POST');
        $this->form->expects($this->once())
            ->method('submit')
            ->with(self::FORM_DATA);
        $this->form->expects($this->once())
            ->method('isValid')
            ->will($this->returnValue(true));

        $em = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();
        $em->expects($this->once())
            ->method('persist')
            ->with($data);
        $em->expects($this->once())
            ->method('flush')
            ->willThrowException(new \Exception('Test flush exception'));
        $this->doctrineHelper->expects($this->once())
            ->method('getEntityManager')
            ->with($data)
            ->will($this->returnValue($em));

        $this->handler->update($data, $this->form, 'Saved');
    }

    /**
     * Test should not be called because handler does not trigger form events
     */
    public function testHandleUpdateBeforeFormDataSetInterrupted()
    {
    }

    /**
     * Test should not be called because handler does not trigger form events
     */
    public function testHandleUpdateInterruptedBeforeFormSubmit()
    {
    }

    /**
     * Test should not be called because handler does not trigger form events
     */
    public function testUpdateInterruptedBeforeFormSubmit()
    {
    }

    /**
     * @return object
     */
    protected function getObject()
    {
        return new Customer();
    }
}
