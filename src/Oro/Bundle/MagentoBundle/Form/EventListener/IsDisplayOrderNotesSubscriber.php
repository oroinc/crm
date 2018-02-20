<?php

namespace Oro\Bundle\MagentoBundle\Form\EventListener;

use Oro\Bundle\FormBundle\Utils\FormUtils;
use Oro\Bundle\MagentoBundle\Entity\MagentoTransport;
use Oro\Bundle\MagentoBundle\Form\Type\AbstractTransportSettingFormType;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;

class IsDisplayOrderNotesSubscriber implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::PRE_SET_DATA => 'processIsDisplayOrderNotesFieldOnPreSet',
            FormEvents::PRE_SUBMIT   => 'processIsDisplayOrderNotesOnPreSubmit'
        ];
    }

    /**
     * Block field "isDisplayOrderNotes" in case when required version of extension isn't installed
     * or in case when we create new integration
     *
     * @param FormEvent $event
     */
    public function processIsDisplayOrderNotesFieldOnPreSet(FormEvent $event)
    {
        $form = $event->getForm();

        /**
         * @var $data MagentoTransport
         */
        $data = $event->getData();
        if ($data === null) {
            return;
        }

        if (null === $data->getId() || true !== $data->isSupportedOrderNoteExtensionVersion()) {
            $this->switchDisabledOptionIsDisplayOrderNotesField($form, true);
        }
    }

    /**
     * @param FormEvent $event
     *
     * @return mixed
     */
    public function processIsDisplayOrderNotesOnPreSubmit(FormEvent $event)
    {
        $form = $event->getForm();

        /**
         * We need to enable field, because another way we won't have possibility
         * to save value to it in case when extension is exist on Magento side
         * and supports order note functionality.
         * We need do this because preSet event fires before preSubmit and it did block of this field.
         */
        $this->switchDisabledOptionIsDisplayOrderNotesField($form, false);
    }

    /**
     * @param FormInterface $form
     * @param boolean       $disabledOptionValue
     */
    private function switchDisabledOptionIsDisplayOrderNotesField(FormInterface $form, $disabledOptionValue)
    {
        if (!$form->has(AbstractTransportSettingFormType::IS_DISPLAY_ORDER_NOTES_FIELD_NAME)) {
            return;
        }

        FormUtils::replaceField(
            $form,
            AbstractTransportSettingFormType::IS_DISPLAY_ORDER_NOTES_FIELD_NAME,
            [
                'disabled' => $disabledOptionValue
            ]
        );
    }
}
