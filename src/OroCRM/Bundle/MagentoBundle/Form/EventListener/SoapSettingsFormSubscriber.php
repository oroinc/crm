<?php

namespace OroCRM\Bundle\MagentoBundle\Form\EventListener;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Oro\Bundle\FormBundle\Utils\FormUtils;
use Oro\Bundle\SecurityBundle\Encoder\Mcrypt;
use Oro\Bundle\IntegrationBundle\Entity\Status;
use Oro\Bundle\IntegrationBundle\Entity\Channel;

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

        $this->muteFields($form);

        if ($data->getId()) {
            // change label for apiKey field
            FormUtils::replaceField($form, 'apiKey', ['label' => 'New SOAP API Key', 'required' => false]);
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

        $this->muteFields($form);

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

    /**
     * Disable fields that are not allowed to be modified since channel has at least one sync completed
     *
     * @param FormInterface $form
     */
    protected function muteFields(FormInterface $form)
    {
        if ($form->getParent()) {
            /** @var Channel $channel */
            $channel = $form->getParent()->getData();

            if (!($channel && $channel->getId())) {
                // do nothing if channel is new
                return;
            }

            $atLeastOneSync = $channel->getStatuses()->exists(
                function ($key, Status $status) {
                    return intval($status->getCode()) === Status::STATUS_COMPLETED;
                }
            );
            if ($atLeastOneSync) {
                // disable start sync date
                FormUtils::replaceField($form, 'syncStartDate', ['disabled' => true]);
                // disable websites selector
                FormUtils::replaceField($form, 'websiteId', ['disabled' => true]);
            }
        }
    }
}
