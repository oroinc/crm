<?php

namespace Oro\Bundle\ChannelBundle\Tests\Unit\EventListener;

use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\ChannelBundle\EventListener\EmbeddedFormListener;
use Oro\Bundle\ChannelBundle\Model\ChannelAwareInterface;
use Oro\Bundle\ChannelBundle\Tests\Unit\Stubs\Entity\EmbeddedFormStub;
use Oro\Bundle\EmbeddedFormBundle\Event\EmbeddedFormSubmitBeforeEvent;
use Oro\Bundle\UIBundle\Event\BeforeFormRenderEvent;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Environment;

class EmbeddedFormListenerTest extends \PHPUnit\Framework\TestCase
{
    /** @var Request */
    private $request;

    /** @var RequestStack */
    private $requestStack;

    /** @var EmbeddedFormListener */
    private $listener;

    protected function setUp(): void
    {
        $this->request = new Request([], [], ['_route' => 'oro_embedded_form_']);
        $this->requestStack = new RequestStack();

        $this->listener = new EmbeddedFormListener($this->requestStack);
    }

    public function testAddDataChannelField()
    {
        $env = $this->createMock(Environment::class);
        $newField = "<input>";

        $env->expects($this->once())
            ->method('render')
            ->willReturn($newField);

        $formView = $this->createMock(FormView::class);
        $currentFormData = 'someHTML';
        $formData = [
            'dataBlocks' => [
                [
                    'subblocks' => [
                        ['data' => [$currentFormData]]
                    ]
                ]
            ]
        ];

        $event = $this->createMock(BeforeFormRenderEvent::class);
        $event->expects($this->once())
            ->method('getTwigEnvironment')
            ->willReturn($env);
        $event->expects($this->once())
            ->method('getFormData')
            ->willReturn($formData);
        $event->expects($this->once())
            ->method('getForm')
            ->willReturn($formView);

        array_unshift($formData['dataBlocks'][0]['subblocks'][0]['data'], $newField);
        $event->expects($this->once())
            ->method('setFormData')
            ->with($formData);

        $this->requestStack->push($this->request);

        $this->listener->addDataChannelField($event);
    }

    public function testAddDataChannelFieldNoRequest()
    {
        $event = $this->createMock(BeforeFormRenderEvent::class);
        $event->expects($this->never())
            ->method($this->anything());
        $this->listener->addDataChannelField($event);
    }

    public function testOnEmbeddedFormSubmit()
    {
        $formEntity = $this->createMock(EmbeddedFormStub::class);
        $formEntity->expects($this->never())
            ->method('getDataChannel');
        $event = new EmbeddedFormSubmitBeforeEvent([], $formEntity);

        $this->requestStack->push($this->request);

        $this->listener->onEmbeddedFormSubmit($event);
    }

    public function testOnEmbeddedFormSubmitWithDataChannel()
    {
        $formEntity = $this->createMock(EmbeddedFormStub::class);

        $dataChannel = $this->createMock(Channel::class);
        $formEntity->expects($this->once())
            ->method('getDataChannel')
            ->willReturn($dataChannel);
        $data = $this->createMock(ChannelAwareInterface::class);
        $data->expects($this->once())
            ->method('setDataChannel');
        $event = new EmbeddedFormSubmitBeforeEvent($data, $formEntity);

        $this->requestStack->push($this->request);

        $this->listener->onEmbeddedFormSubmit($event);
    }
}
