<?php

namespace Oro\Bundle\MagentoBundle\Form\EventListener;

use Oro\Bundle\FormBundle\Utils\FormUtils;
use Oro\Bundle\MagentoBundle\Entity\MagentoTransport;
use Oro\Bundle\SecurityBundle\Encoder\SymmetricCrypterInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;

/**
 * Class handles settings modification on integration page
 */
class SettingsFormSubscriber implements EventSubscriberInterface
{
    /**
     * @var SymmetricCrypterInterface
     */
    protected $encryptor;

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
     * @param SymmetricCrypterInterface $encryptor
     */
    public function __construct(SymmetricCrypterInterface $encryptor)
    {
        $this->encryptor = $encryptor;
    }

    /**
     * Populate websites choices if exist in entity
     *
     * @param FormEvent $event
     */
    public function preSet(FormEvent $event)
    {
        $form = $event->getForm();
        /**
         * @var $data MagentoTransport
         */
        $data = $event->getData();

        if ($data === null) {
            return;
        }

        $websites = $data->getWebsites();

        if (is_array($websites)) {
            $this->modifyWebsitesList($form, $websites);
        }

        if ($data->getId()) {
            FormUtils::replaceField(
                $form,
                'apiKey',
                ['required' => false],
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

        // first time all websites comes from frontend(when run sync action)
        // otherwise loaded from entity
        if (!empty($data['websites'])) {
            $websites = $data['websites'];
            // reverseTransform, but not set back to event
            if (!is_array($websites)) {
                $websites = json_decode($websites, true);
            }

            if (is_array($websites)) {
                $this->modifyWebsitesList($form, $websites);
            }
        }

        $oldPassword = $form->get('apiKey')->getData();
        if (empty($data['apiKey']) && $oldPassword) {
            // populate old password
            $data['apiKey'] = $oldPassword;
        } elseif (isset($data['apiKey'])) {
            $data['apiKey'] = $this->encryptor->encryptData($data['apiKey']);
        }

        $event->setData($data);
    }

    /**
     * @param FormInterface $form
     * @param array         $websites
     *
     * @return void
     */
    protected function modifyWebsitesList(FormInterface $form, array $websites)
    {
        if (empty($websites)) {
            return;
        }
        $choices = [];
        foreach ($websites as $website) {
            if (\is_array($website) && isset($website['id'], $website['label'])) {
                $choices[$website['label']] = $website['id'];
            }
        }

        FormUtils::replaceField($form, 'websiteId', ['choices' => $choices]);
    }
}
