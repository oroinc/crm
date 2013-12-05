<?php

namespace OroCRM\Bundle\MagentoBundle\Form\EventListener;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Oro\Bundle\SecurityBundle\Encoder\Mcrypt;

class SoapSettingsFormSubscriber implements EventSubscriberInterface
{
    /** @var Mcrypt */
    protected $encryptor;

    public function __construct(Mcrypt $encryptor)
    {
        $this->encryptor = $encryptor;
    }

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

        $modifier = $this->getModifierWebsitesList($data->getWebsites());
        $modifier($form);

        if ($data->getId()) {
            // change label for apiKey field
            $options = $event->getForm()->get('apiKey')->getConfig()->getOptions();
            $options = array_merge($options, ['label' => 'New SOAP API Key', 'required' => false]);
            $form->add('apiKey', 'password', $options);
        }
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

        $oldPassword = $event->getForm()->get('apiKey')->getData();
        if (empty($data['apiKey']) && $oldPassword) {
            // populate old password
            $data['apiKey'] = $oldPassword;
        } elseif (isset($data['apiKey'])) {
            $data['apiKey'] = $this->encryptor->encryptData($data['apiKey']);
        }

        if (!empty($data['websites'])) {
            $websites = $data['websites'];
            // reverseTransform, but not set back to event
            if (!is_array($websites)) {
                $websites = json_decode($websites, true);
            }
            $modifier = $this->getModifierWebsitesList($websites);
            $modifier($event->getForm());
        }

        $event->setData($data);
    }

    /**
     * @param array $websites
     *
     * @return callable
     */
    protected function getModifierWebsitesList($websites)
    {
        return function (FormInterface $form) use ($websites) {
            if (empty($websites)) {
                return;
            }

            if ($form->has('websiteId')) {
                $config = $form->get('websiteId')->getConfig()->getOptions();
                unset($config['choice_list']);
                unset($config['choices']);
            } else {
                $config = array();
            }

            if (array_key_exists('auto_initialize', $config)) {
                $config['auto_initialize'] = false;
            }

            $choices = [];
            foreach ($websites as $website) {
                $choices[$website['id']] = $website['label'];
            }

            $form->add('websiteId', 'choice', array_merge($config, ['choices' => $choices]));
        };
    }
}
