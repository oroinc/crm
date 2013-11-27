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
     * Populate store choices if exist in entity
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

        $modifier = $this->getModifierStoresList($data->getStores());
        $modifier($form);
    }

    /**
     * Pre submit event listener
     * Encrypt passwords and populate if empty
     * Populate store choices from hidden fields
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

        if (!empty($data['stores'])) {
            $stores = $data['stores'];
            // reverseTransform, but not set back to event
            if (!is_array($stores)) {
                $stores = json_decode($stores, true);
            }
            $modifier = $this->getModifierStoresList($stores);
            $modifier($event->getForm());
        }

        $event->setData($data);
    }

    /**
     * @param array $stores
     *
     * @return callable
     */
    protected function getModifierStoresList($stores)
    {
        return function (FormInterface $form) use ($stores) {
            if (empty($stores)) {
                return;
            }

            if ($form->has('store_id')) {
                $config = $form->get('store_id')->getConfig()->getOptions();
                unset($config['choice_list']);
                unset($config['choices']);
            } else {
                $config = array();
            }

            if (array_key_exists('auto_initialize', $config)) {
                $config['auto_initialize'] = false;
            }

            $choices = [];
            foreach ($stores as $store) {
                $choices[$store['id']] = $store['name'];
            }

            $form->add('store_id', 'choice', array_merge($config, ['choices' => $choices]));
        };
    }
}
