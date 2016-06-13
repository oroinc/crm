<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\Form\Handler;

use Symfony\Component\Form\Form;

use OroCRM\Bundle\MagentoBundle\Service\CustomerStateHandler;
use OroCRM\Bundle\MagentoBundle\Service\StateManager;
use Oro\Bundle\FormBundle\Tests\Unit\Model\UpdateHandlerTest;
use OroCRM\Bundle\MagentoBundle\Entity\Customer;
use OroCRM\Bundle\MagentoBundle\Form\Handler\CustomerHandler;

class CustomerHandlerTest extends UpdateHandlerTest
{
    protected function setUp()
    {
        parent::setUp();

        $this->handler = new CustomerHandler(
            $this->request,
            $this->session,
            $this->router,
            $this->doctrineHelper,
            $this->eventDispatcher
        );
        $this->handler->setStateHandler(new CustomerStateHandler(new StateManager($this->doctrineHelper)));
    }

    public function testHandleUpdateWorksWithValidForm()
    {
        $entity = $this->getObject();

        $this->request->expects($this->once())
            ->method('getMethod')
            ->will($this->returnValue('POST'));
        $this->form->expects($this->once())
            ->method('submit')
            ->with($this->request);
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
        $this->request->expects($this->once())
            ->method('getMethod')
            ->will($this->returnValue('POST'));
        $this->form->expects($this->once())
            ->method('submit')
            ->with($this->request);
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
        $this->request->expects($this->once())
            ->method('getMethod')
            ->will($this->returnValue('POST'));
        $this->form->expects($this->once())
            ->method('submit')
            ->with($this->request);
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
        /** @var \PHPUnit_Framework_MockObject_MockObject|Form $form */
        $form = $this->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->getMock();
        $entity = $this->getObject();
        $form->expects($this->once())
            ->method('setData')
            ->with($entity);
        $this->request->expects($this->once())
            ->method('getMethod')
            ->will($this->returnValue('POST'));
        $form->expects($this->once())
            ->method('submit')
            ->with($this->request);
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

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Test flush exception
     */
    public function testHandleUpdateWorksWhenFormFlushFailed()
    {
        $entity = $this->getObject();
        $this->form->expects($this->once())
            ->method('setData')
            ->with($entity);
        $this->request->expects($this->once())
            ->method('getMethod')
            ->will($this->returnValue('POST'));
        $this->form->expects($this->once())
            ->method('submit')
            ->with($this->request);
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

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Test flush exception
     */
    public function testUpdateWorksWhenFormFlushFailed()
    {
        $data = $this->getObject();
        $this->form->expects($this->once())
            ->method('setData')
            ->with($data);
        $this->request->expects($this->once())
            ->method('getMethod')
            ->will($this->returnValue('POST'));
        $this->form->expects($this->once())
            ->method('submit')
            ->with($this->request);
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
