<?php

namespace OroCRM\Bundle\ChannelBundle\Tests\Unit\EventListener;

use OroCRM\Bundle\ChannelBundle\EventListener\EmbeddedFormListener;

class EmbeddedFormListenerTest extends \PHPUnit_Framework_TestCase
{
    public function testAddDataChannelField()
    {
        $env      = $this->getMockBuilder('Twig_Environment')
            ->disableOriginalConstructor()
            ->getMock();
        $newField = "<input>";

        $env->expects($this->once())
            ->method('render')
            ->will($this->returnValue($newField));

        $formView        = $this->getMockBuilder('Symfony\Component\Form\FormView')
            ->disableOriginalConstructor()
            ->getMock();
        $currentFormData = 'someHTML';
        $formData        = [
            'dataBlocks' => [
                [
                    'subblocks' => [
                        ['data' => [$currentFormData]]
                    ]
                ]
            ]
        ];

        $event = $this->getMockBuilder('Oro\Bundle\UIBundle\Event\BeforeFormRenderEvent')
            ->disableOriginalConstructor()
            ->getMock();
        $event->expects($this->once())
            ->method('getTwigEnvironment')
            ->will($this->returnValue($env));
        $event->expects($this->once())
            ->method('getFormData')
            ->will($this->returnValue($formData));
        $event->expects($this->once())
            ->method('getForm')
            ->will($this->returnValue($formView));

        $formData['dataBlocks'][0]['subblocks'][0]['data'][] = $newField;
        $event->expects($this->once())->method('setFormData')->with($formData);

        $provider = $this->getMockBuilder('Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider')
            ->disableOriginalConstructor()
            ->getMock();
        $provider->expects($this->any())
            ->method('hasConfig')
            ->will($this->returnValue(false));

        $configManager = $this->getMockBuilder('Oro\Bundle\EntityConfigBundle\Config\ConfigManager')
            ->disableOriginalConstructor()
            ->getMock();
        $configManager->expects($this->any())
            ->method('getProvider')
            ->will($this->returnValue($provider));

        $listener = new EmbeddedFormListener($configManager);
        $listener->addDataChannelField($event);
    }
}
