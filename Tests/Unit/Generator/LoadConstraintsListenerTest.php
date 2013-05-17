<?php

namespace Oro\Bundle\JsFormValidationBundle\Tests\Unit\DependencyInjection;

use Symfony\Component\Form\FormView;
use Symfony\Component\Validator\Mapping\ClassMetadata;

use APY\JsFormValidationBundle\Generator\PreProcessEvent;

use Oro\Bundle\JsFormValidationBundle\EventListener\LoadConstraintsListener;

class LoadConstraintsListenerTest extends \PHPUnit_Framework_TestCase
{
    public function testOnJsfvPreProcess()
    {
        $formView = $this->getMock('Symfony\Component\Form\FormView');
        $metadata = $this->getMockBuilder('Symfony\Component\Validator\Mapping\ClassMetadata')
            ->setMethods(array('addConstraint'))
            ->disableOriginalConstructor()
            ->getMock();

        $event = new PreProcessEvent($formView, $metadata);

        $listener = new LoadConstraintsListener();
        $listener->onJsfvPreProcess($event);
    }
}
