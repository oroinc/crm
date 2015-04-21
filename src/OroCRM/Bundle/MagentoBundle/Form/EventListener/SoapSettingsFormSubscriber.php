<?php

namespace OroCRM\Bundle\MagentoBundle\Form\EventListener;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Oro\Bundle\FormBundle\Utils\FormUtils;
use Oro\Bundle\SecurityBundle\Encoder\Mcrypt;

class SoapSettingsFormSubscriber implements EventSubscriberInterface
{
    /** @var Mcrypt */
    protected $encryptor;

    /**
     * @param Mcrypt $encryptor
     */
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
            FormUtils::replaceField(
                $form,
                'apiKey',
                ['label' => 'orocrm.magento.magentosoaptransport.new_api_key.label', 'required' => false],
                ['constraints']
            );
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
        $form = $event->getForm();

        $oldPassword = $form->get('apiKey')->getData();
        if (empty($data['apiKey']) && $oldPassword) {
            // populate old password
            $data['apiKey'] = $oldPassword;
        } elseif (isset($data['apiKey'])) {
            $data['apiKey'] = $this->encryptor->encryptData($data['apiKey']);
        }

        // first time all websites comes from frontend(when run sync action)
        // otherwise loaded from entity
        if (!empty($data['websites'])) {
            $websites = $data['websites'];
            // reverseTransform, but not set back to event
            if (!is_array($websites)) {
                $websites = json_decode($websites, true);
            }
            $modifier = $this->getModifierWebsitesList($websites);
            $modifier($form);
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

            $choices = [];
            foreach ($websites as $website) {
                $choices[$website['id']] = $website['label'];
            }

            FormUtils::replaceField($form, 'websiteId', ['choices' => $choices], ['choice_list']);
        };
    }
}
