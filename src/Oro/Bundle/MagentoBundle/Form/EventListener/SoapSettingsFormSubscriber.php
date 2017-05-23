<?php

namespace Oro\Bundle\MagentoBundle\Form\EventListener;

use Symfony\Component\Form\FormEvent;

use Oro\Bundle\FormBundle\Utils\FormUtils;
use Oro\Bundle\SecurityBundle\Encoder\Mcrypt;

class SoapSettingsFormSubscriber extends SettingsFormSubscriber
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

    /** @inheritdoc */
    public function preSet(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();

        if ($data === null) {
            return;
        }

        parent::preSet($event);

        if ($data->getId()) {
            // change label for apiKey field
            FormUtils::replaceField(
                $form,
                'apiKey',
                ['label' => 'oro.magento.magentotransport.soap.new_api_key.label', 'required' => false],
                ['constraints']
            );
        }
    }

    /** @inheritdoc */
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

        $event->setData($data);

        parent::preSubmit($event);
    }
}
