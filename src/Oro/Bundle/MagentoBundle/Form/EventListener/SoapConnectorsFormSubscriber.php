<?php

namespace Oro\Bundle\MagentoBundle\Form\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;

use Oro\Bundle\IntegrationBundle\Form\Type\ChannelType;
use Oro\Bundle\IntegrationBundle\Manager\TypesRegistry;
use Oro\Bundle\IntegrationBundle\Provider\ConnectorInterface;
use Oro\Bundle\MagentoBundle\Provider\ExtensionAwareInterface;

class SoapConnectorsFormSubscriber implements EventSubscriberInterface
{
    /** @var TypesRegistry */
    protected $typeRegistry;

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
     * Populate websites choices if exist in entity
     *
     * @param FormEvent $event
     */
    public function preSet(FormEvent $event)
    {
        $this->modify($event->getData(), $event->getForm()->getParent());
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
                unset($config['choice_list']);
                unset($config['choices']);
                /**
                 * @todo: should be removed in scope of BAP-11222
                 */
                /* Check if right now we're using Symfony 2.8+ */
                if (method_exists('Symfony\Component\Form\AbstractType', 'getBlockPrefix')) {
                    unset($config['choice_label']);
                }
            } else {
                $config = [];
            }

            if (array_key_exists('auto_initialize', $config)) {
                $config['auto_initialize'] = false;
            }

            $allowedTypesChoices = $this->typeRegistry->getAvailableConnectorsTypesChoiceList(
                'magento',
                function (ConnectorInterface $connector) use ($data) {
                    return $connector instanceof ExtensionAwareInterface ? $data : true;
                }
            );

            $form->getParent()
                ->add('connectors', 'choice', array_merge($config, ['choices' => $allowedTypesChoices]));
        }
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
