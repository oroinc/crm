<?php

namespace OroCRM\Bundle\ChannelBundle\EventListener;

use Doctrine\Common\Util\ClassUtils;

use Oro\Bundle\UIBundle\Event\BeforeFormRenderEvent;
use Oro\Bundle\EntityConfigBundle\Config\ConfigManager;

class EmbeddedFormListener
{
    /** @var ConfigManager  */
    protected $configManager;

    /**
     * @param ConfigManager $configManager
     */
    public function __construct(ConfigManager $configManager)
    {
        $this->configManager = $configManager;
    }

    /**
     * Add owner field to forms
     *
     * @param BeforeFormRenderEvent $event
     */
    public function addDataChannelField(BeforeFormRenderEvent $event)
    {
        $environment    = $event->getTwigEnvironment();
        $data           = $event->getFormData();
        $form           = $event->getForm();
        $label          = false;
        $entityProvider = $this->configManager->getProvider('entity');

        if (is_object($form->vars['value'])) {
            $className = ClassUtils::getClass($form->vars['value']);
            if (class_exists($className)
                && $entityProvider->hasConfig($className, 'dataChannel')
            ) {
                $config = $entityProvider->getConfig($className, 'dataChannel');
                $label  = $config->get('label');
            }
        }

        $dataChannelField = $environment->render(
            "OroCRMChannelBundle:Form:dataChannelField.html.twig",
            [
                'form'  => $form,
                'label' => $label
            ]
        );

        /**
         * Setting dataChannel field as first field in first data block
         */
        if (!empty($data['dataBlocks'])) {
            if (isset($data['dataBlocks'][0]['subblocks'])) {
                array_unshift($data['dataBlocks'][0]['subblocks'][0]['data'], $dataChannelField);
            }
        }

        $event->setFormData($data);
    }
}
