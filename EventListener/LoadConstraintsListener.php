<?php

namespace Oro\Bundle\JsFormValidationBundle\EventListener;

use Symfony\Component\Validator\Mapping\ClassMetadata;

use APY\JsFormValidationBundle\Generator\PreProcessEvent;

class LoadConstraintsListener
{
    public function onJsfvPreProcess(PreProcessEvent $event)
    {
        $this->loadConstraints($event->getMetaData());
    }

    protected function loadConstraints(ClassMetadata $metadata)
    {
        // TODO Load constraints from yml
    }
}
