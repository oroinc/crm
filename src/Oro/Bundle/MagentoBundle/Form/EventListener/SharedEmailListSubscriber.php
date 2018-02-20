<?php

namespace Oro\Bundle\MagentoBundle\Form\EventListener;

use Oro\Bundle\FormBundle\Utils\FormUtils;
use Oro\Bundle\MagentoBundle\Entity\MagentoTransport;
use Oro\Bundle\MagentoBundle\Form\Type\AbstractTransportSettingFormType;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;

class SharedEmailListSubscriber implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::PRE_SET_DATA => 'processSharedGuestEmailListFieldOnPreSet',
            FormEvents::PRE_SUBMIT   => 'processSharedGuestEmailListFieldOnPreSubmit'
        ];
    }

    /**
     * Block field "sharedGuestEmailList" in case when extension is not installed
     * or in case when we create new integration
     *
     * @param FormEvent $event
     */
    public function processSharedGuestEmailListFieldOnPreSet(FormEvent $event)
    {
        $form = $event->getForm();

        /**
         * @var $data MagentoTransport
         */
        $data = $event->getData();
        if ($data === null) {
            return;
        }

        if (null === $data->getId() || true !== $data->getIsExtensionInstalled()) {
            $this->switchDisabledOptionSharedEmailField($form, true);
        }
    }

    /**
     * @param FormEvent $event
     *
     * @return mixed
     */
    public function processSharedGuestEmailListFieldOnPreSubmit(FormEvent $event)
    {
        $data = (array)$event->getData();
        $form = $event->getForm();

        /**
         * We need to enable field, because another way we won't have possibility
         * to save value to it in case when extension is exist on Magento side.
         * We need do this because preSet event fires before preSubmit and it did block of this field.
         */
        $this->switchDisabledOptionSharedEmailField($form, false);

        /**
         * Clear field value in case when extension is not accessible
         */
        if (empty($data['isExtensionInstalled'])) {
            $data[AbstractTransportSettingFormType::SHARED_GUEST_EMAIL_FIELD_NAME] = '';
        }

        $event->setData($data);
    }

    /**
     * @param FormInterface $form
     * @param boolean       $disabledOptionValue
     */
    private function switchDisabledOptionSharedEmailField(FormInterface $form, $disabledOptionValue)
    {
        if (!$form->has(AbstractTransportSettingFormType::SHARED_GUEST_EMAIL_FIELD_NAME)) {
            return;
        }

        FormUtils::replaceField(
            $form,
            AbstractTransportSettingFormType::SHARED_GUEST_EMAIL_FIELD_NAME,
            [
                'disabled' => $disabledOptionValue
            ]
        );
    }
}
