<?php

namespace Oro\Bundle\MagentoBundle\Form\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;

use Oro\Bundle\FormBundle\Utils\FormUtils;

class SettingsFormSubscriber implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::PRE_SET_DATA => 'preSet',
            FormEvents::PRE_SUBMIT   => 'preSubmit'
        ];
    }

    /**
     * Populate websites choices if exist in entity
     *
     * @param FormEvent $event
     */
    public function preSet(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();

        if ($data === null) {
            return;
        }

        $this->modifyWebsitesList($form, $data->getWebsites());
    }

    /**
     * Pre submit event listener
     * Encrypt passwords and populate if empty
     * Populate websites choices from hidden fields
     *
     * @param FormEvent $event
     */
    public function preSubmit(FormEvent $event)
    {
        $data = (array)$event->getData();
        $form = $event->getForm();

        // first time all websites comes from frontend(when run sync action)
        // otherwise loaded from entity
        if (!empty($data['websites'])) {
            $websites = $data['websites'];
            // reverseTransform, but not set back to event
            if (!is_array($websites)) {
                $websites = json_decode($websites, true);
            }
            $this->modifyWebsitesList($form, $websites);
        }

        $event->setData($data);
    }

    /**
     * @param array $websites
     *
     * @return void
     */
    protected function modifyWebsitesList(FormInterface $form, $websites)
    {
        if (empty($websites)) {
            return;
        }
        $choices = [];
        foreach ($websites as $website) {
            $choices[$website['label']] = $website['id'];
        }

        FormUtils::replaceField($form, 'websiteId', ['choices' => $choices], ['choice_list']);
    }
}
