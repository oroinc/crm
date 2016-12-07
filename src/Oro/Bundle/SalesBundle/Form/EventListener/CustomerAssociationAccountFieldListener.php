<?php

namespace Oro\Bundle\SalesBundle\Form\EventListener;

use Doctrine\Common\Util\ClassUtils;

use Oro\Bundle\EntityConfigBundle\Config\ConfigManager;
use Oro\Bundle\UIBundle\Event\BeforeFormRenderEvent;

class CustomerAssociationAccountFieldListener
{
    /**
     * @param BeforeFormRenderEvent $event
     */
    public function addAccountField(BeforeFormRenderEvent $event)
    {
        $environment = $event->getTwigEnvironment();
        $data = $event->getFormData();
        $form = $event->getForm();

        $ownerField = $environment->render(
            "OroSalesBundle:Customer:accountField.html.twig",
            ['form'  => $form]
        );

        /**
         * Setting owner field as first field in first data block
         */
        if (!empty($data['dataBlocks'])) {
            if (isset($data['dataBlocks'][0]['subblocks'])) {
                if (!isset($data['dataBlocks'][0]['subblocks'][0])) {
                    $data['dataBlocks'][0]['subblocks'][0] = ['data' => []];
                }
                array_unshift($data['dataBlocks'][0]['subblocks'][0]['data'], $ownerField);
            }
        }

        $event->setFormData($data);
    }
}
