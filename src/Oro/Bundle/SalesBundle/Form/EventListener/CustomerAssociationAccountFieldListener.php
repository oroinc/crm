<?php

namespace Oro\Bundle\SalesBundle\Form\EventListener;

use Oro\Bundle\UIBundle\Event\BeforeFormRenderEvent;

/**
 * Listener adds customer association account field.
 */
class CustomerAssociationAccountFieldListener
{
    public function addAccountField(BeforeFormRenderEvent $event)
    {
        $environment = $event->getTwigEnvironment();
        $data = $event->getFormData();
        $form = $event->getForm();

        $accountField = $environment->render(
            "@OroSales/Customer/accountField.html.twig",
            ['form'  => $form]
        );
        // set account field as first, in general block, but current listener has higher priority than owner field,
        // this means that owner field will be set after account field
        if (!empty($data['dataBlocks'])) {
            if (isset($data['dataBlocks'][0]['subblocks'])) {
                if (!isset($data['dataBlocks'][0]['subblocks'][0])) {
                    $data['dataBlocks'][0]['subblocks'][0] = ['data' => []];
                }
                array_unshift($data['dataBlocks'][0]['subblocks'][0]['data'], $accountField);
            }
        }

        $event->setFormData($data);
    }
}
