<?php

namespace OroCRM\Bundle\MarketingListBundle\Tests\Unit\Form\Handler;

use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ValidatorInterface;

use Oro\Bundle\SegmentBundle\Entity\SegmentType;
use Oro\Bundle\UserBundle\Entity\User;
use OroCRM\Bundle\MarketingListBundle\Entity\MarketingList;
use OroCRM\Bundle\MarketingListBundle\Entity\MarketingListType;
use OroCRM\Bundle\MarketingListBundle\Form\Handler\MarketingListHandler;

class MarketingListHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MarketingListHandler
     */
    protected $handler;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|Form
     */
    protected $form;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $manager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ValidatorInterface
     */
    protected $validator;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|TranslatorInterface
     */
    protected $translator;

    /**
     * @var MarketingList
     */
    protected $testEntity;

    protected function setUp()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|RegistryInterface $registry */
        $registry = $this->getMockForAbstractClass('Symfony\Bridge\Doctrine\RegistryInterface');

        $this->manager = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();
        $registry->expects($this->once())
            ->method('getManager')
            ->will($this->returnValue($this->manager));

        $this->form = $this->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->getMock();

        $this->request = new Request();

        $this->validator = $this->getMock('Symfony\Component\Validator\ValidatorInterface');
        $this->translator = $this->getMock('Symfony\Component\Translation\TranslatorInterface');

        $this->testEntity = new MarketingList();
        $this->handler = new MarketingListHandler(
            $this->form,
            $this->request,
            $registry,
            $this->validator,
            $this->translator
        );
        $this->form->expects($this->once())
            ->method('setData')
            ->with($this->testEntity);
    }

    public function testProcess()
    {
        $this->request->setMethod('POST');
        $this->form->expects($this->once())
            ->method('submit')
            ->with($this->request);

        $this->assertProcessSegment();

        $this->form->expects($this->once())
            ->method('isValid')
            ->will($this->returnValue(true));

        $this->translator
            ->expects($this->once())
            ->method('trans')
            ->with($this->isType('string'), $this->isType('array'))
            ->will($this->returnValue('Marketing List test segment'));

        $this->form->expects($this->once())
            ->method('getName')
            ->will($this->returnValue('test_form'));

        $this->manager->expects($this->once())
            ->method('persist')
            ->with($this->testEntity);
        $this->manager->expects($this->once())
            ->method('flush');

        $this->assertTrue($this->handler->process($this->testEntity));

        $this->assertSegmentData();
    }

    public function testProcessInvalidEntity()
    {
        $this->request->setMethod('POST');
        $this->form->expects($this->once())
            ->method('submit')
            ->with($this->request);

        $this->assertProcessSegment();

        $this->form->expects($this->once())
            ->method('isValid')
            ->will($this->returnValue(false));

        $this->translator
            ->expects($this->once())
            ->method('trans')
            ->with($this->isType('string'), $this->isType('array'))
            ->will($this->returnValue('Marketing List test segment'));

        $this->manager->expects($this->never())
            ->method('persist');
        $this->manager->expects($this->never())
            ->method('flush');

        $this->assertFalse($this->handler->process($this->testEntity));

        $this->assertSegmentData();
    }

    public function testProcessInvalidSegment()
    {
        $this->request->setMethod('POST');
        $this->form->expects($this->once())
            ->method('submit')
            ->with($this->request);

        $this->assertProcessSegment();

        $this->form->expects($this->once())
            ->method('isValid')
            ->will($this->returnValue(false));

        $violation = $this->getMockForAbstractClass('Symfony\Component\Validator\ConstraintViolationInterface');
        $violation->expects($this->once())
            ->method('getMessage')
            ->will($this->returnValue('message'));
        $violation->expects($this->once())
            ->method('getMessageTemplate')
            ->will($this->returnValue('message template'));
        $violation->expects($this->once())
            ->method('getMessageParameters')
            ->will($this->returnValue(['test']));
        $violation->expects($this->once())
            ->method('getMessagePluralization')
            ->will($this->returnValue('message pluralization'));
        $errors = new ConstraintViolationList([$violation]);

        $this->validator->expects($this->once())
            ->method('validate')
            ->with($this->isInstanceOf('Oro\Bundle\SegmentBundle\Entity\Segment'), ['Default', 'marketing_list'])
            ->will($this->returnValue($errors));

        $this->translator
            ->expects($this->once())
            ->method('trans')
            ->with($this->isType('string'), $this->isType('array'))
            ->will($this->returnValue('Marketing List test segment'));

        $this->form->expects($this->once())
            ->method('addError')
            ->with(
                new FormError(
                    'message',
                    'message template',
                    ['test'],
                    'message pluralization'
                )
            );

        $this->manager->expects($this->never())
            ->method('persist');
        $this->manager->expects($this->never())
            ->method('flush');

        $this->assertFalse($this->handler->process($this->testEntity));

        $this->assertSegmentData();
    }

    protected function assertProcessSegment()
    {
        $formData = [
            'definition' => 'test'
        ];

        $this->form->expects($this->once())
            ->method('getName')
            ->will($this->returnValue('test_form'));

        $this->request->request->set('test_form', $formData);
        $businessUnit = $this->getMockBuilder('Oro\Bundle\OrganizationBundle\Entity\BusinessUnit')
            ->disableOriginalConstructor()
            ->getMock();

        /** @var \PHPUnit_Framework_MockObject_MockObject|User $owner */
        $owner = $this->getMockBuilder('Oro\Bundle\UserBundle\Entity\User')
            ->disableOriginalConstructor()
            ->getMock();
        $owner->expects($this->atLeastOnce())
            ->method('getOwner')
            ->will($this->returnValue($businessUnit));

        $this->testEntity->setName('test')
            ->setDescription('description')
            ->setType(new MarketingListType(MarketingListType::TYPE_DYNAMIC))
            ->setOwner($owner);

        $segmentType = new SegmentType(SegmentType::TYPE_DYNAMIC);
        $this->manager->expects($this->once())
            ->method('find')
            ->with('OroSegmentBundle:SegmentType', MarketingListType::TYPE_DYNAMIC)
            ->will($this->returnValue($segmentType));
    }

    protected function assertSegmentData()
    {
        $segmentType = new SegmentType(SegmentType::TYPE_DYNAMIC);
        $segment = $this->testEntity->getSegment();
        $this->assertEquals('Marketing List test segment', $segment->getName());
        $this->assertEquals('test', $segment->getDefinition());
        $this->assertEquals($this->testEntity->getOwner()->getOwner(), $segment->getOwner());
        $this->assertEquals($segmentType, $segment->getType());
    }

    public function testProcessWrongRequest()
    {
        $this->request->setMethod('GET');
        $this->assertFalse($this->handler->process($this->testEntity));
    }
}
