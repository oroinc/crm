<?php

namespace Oro\Bundle\MagentoBundle\Form\EventListener;

use Oro\Bundle\IntegrationBundle\Form\Type\ChannelType;
use Oro\Bundle\IntegrationBundle\Manager\TypesRegistry;
use Oro\Bundle\IntegrationBundle\Provider\ConnectorInterface;
use Oro\Bundle\MagentoBundle\Provider\ExtensionAwareInterface;
use Oro\Bundle\MagentoBundle\Provider\MagentoChannelType;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;

class ConnectorsFormSubscriber implements EventSubscriberInterface
{
    /** @var TypesRegistry */
    protected $typeRegistry;

    /**
     * @param TypesRegistry $registry
     */
    public function __construct(TypesRegistry $registry)
    {
        $this->typeRegistry = $registry;
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
     * @param FormEvent $event
     */
    public function preSet(FormEvent $event)
    {
        $this->modify($event->getData(), $event->getForm()->getParent());
    }

    /**
     * @param FormEvent $event
     */
    public function preSubmit(FormEvent $event)
    {
        $this->modify($event->getData(), $event->getForm()->getParent());
    }

    /**
     * @param array         $data
     * @param FormInterface $form
     */
    protected function modify($data, FormInterface $form)
    {
        if ($this->hasChannelParent($form)) {
            $connectors = $form->getParent()->get('connectors');
            if ($connectors) {
                $config = $connectors->getConfig()->getOptions();
                unset($config['choices']);
            } else {
                $config = [];
            }

            if (array_key_exists('auto_initialize', $config)) {
                $config['auto_initialize'] = false;
            }

            $channelType = $this->getFormChannelType($form);

            $allowedTypesChoices = array_flip($this->typeRegistry->getAvailableConnectorsTypesChoiceList(
                $channelType,
                function (ConnectorInterface $connector) use ($data) {
                    return $connector instanceof ExtensionAwareInterface ? $data : true;
                }
            ));

            $form->getParent()
                ->add('connectors', ChoiceType::class, array_merge($config, ['choices' => $allowedTypesChoices]));
        }
    }

    /**
     * @param FormInterface $form
     *
     * @return string
     */
    protected function getFormChannelType(FormInterface $form)
    {
        if ($form->getParent() && $form->getParent()->has('type')) {
            return $form->getParent()->get('type')->getViewData();
        }

        return MagentoChannelType::TYPE;
    }

    /**
     * Check is parent exists and this parent instance of ChannelType
     *
     * @param FormInterface $form
     *
     * @return bool
     */
    private function hasChannelParent(FormInterface $form)
    {
        return (
            $form->getParent()
            && $form->getParent()->getConfig()->getType()->getInnerType() instanceof ChannelType
        );
    }
}
