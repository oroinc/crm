<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\Form\Handler;

use Symfony\Component\Form\Form;

use Oro\Bundle\FormBundle\Tests\Unit\Model\UpdateHandlerTest;
use OroCRM\Bundle\MagentoBundle\Entity\Customer;
use OroCRM\Bundle\MagentoBundle\Form\Handler\CustomerHandler;

class CustomerHandlerTest extends UpdateHandlerTest
{
    protected function setUp()
    {
        parent::setUp();

        $this->handler = new CustomerHandler($this->request, $this->session, $this->router, $this->doctrineHelper);
    }

    public function testSaveFormValid()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|Form $form */
        $form = $this->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->getMock();

        $entity = new Customer();

        $this->request->expects($this->once())
            ->method('getMethod')
            ->will($this->returnValue('POST'));
        $form->expects($this->once())
            ->method('submit')
            ->with($this->request);
        $form->expects($this->once())
            ->method('isValid')
            ->will($this->returnValue(true));
        $em = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();
        $em->expects($this->once())
            ->method('persist')
            ->with(
                $this->callback(
                    function (Customer $processedCustomer) {
                        $this->assertEquals($processedCustomer->getSyncState(), Customer::SYNC_TO_MAGENTO);

                        return true;
                    }
                )
            );
        $em->expects($this->once())
            ->method('flush');
        $this->doctrineHelper->expects($this->once())
            ->method('getEntityManager')
            ->with($entity)
            ->will($this->returnValue($em));
        $this->doctrineHelper->expects($this->once())
            ->method('getSingleEntityIdentifier')
            ->with($entity)
            ->will($this->returnValue(1));

        $expected = $this->assertSaveData($form, $entity);
        $expected['savedId'] = 1;

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
     * @return object
     */
    protected function getObject()
    {
        return new Customer();
    }
}
