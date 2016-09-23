<?php

namespace Oro\Bundle\ChannelBundle\Form\Handler;

use Doctrine\ORM\EntityManager;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;

use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\ChannelBundle\Event\ChannelSaveEvent;
use Oro\Bundle\ChannelBundle\Form\Type\ChannelType;

class ChannelHandler
{
    const UPDATE_MARKER = 'formUpdateMarker';

    /** @var Request */
    protected $request;

    /** @var RegistryInterface */
    protected $registry;

    /** @var FormInterface */
    protected $form;

    /** @var EventDispatcherInterface */
    protected $dispatcher;

    /**
     * @param Request                  $request
     * @param FormInterface            $form
     * @param RegistryInterface        $registry
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(
        Request $request,
        FormInterface $form,
        RegistryInterface $registry,
        EventDispatcherInterface $dispatcher
    ) {
        $this->request    = $request;
        $this->form       = $form;
        $this->registry   = $registry;
        $this->dispatcher = $dispatcher;
    }

    /**
     * @param Channel $entity
     *
     * @return bool
     */
    public function process(Channel $entity)
    {
        $this->handleRequestChannelType($entity);
        $this->form->setData($entity);

        if (in_array($this->request->getMethod(), ['POST', 'PUT'])) {
            $this->form->submit($this->request);

            if (!$this->request->get(self::UPDATE_MARKER, false) && $this->form->isValid()) {
                $this->doSave($entity);

                return true;
            }
        }

        return false;
    }

    /**
     * @param Channel $channel
     */
    protected function handleRequestChannelType(Channel &$channel)
    {
        if ($channel->getChannelType()) {
            return;
        }

        $channelType = $this->request->get(sprintf('%s[channelType]', ChannelType::NAME), null, true);

        if (!$channelType) {
            return;
        }

        $channel->setChannelType($channelType);
    }

    /**
     * Saves entity and dispatches needed events
     *
     * @param Channel $entity
     */
    protected function doSave(Channel $entity)
    {
        $this->getManager()->persist($entity);
        $this->getManager()->flush();

        $this->dispatcher->dispatch(ChannelSaveEvent::EVENT_NAME, new ChannelSaveEvent($entity));
    }

    /**
     * Returns form instance
     *
     * @return FormInterface
     */
    public function getFormView()
    {
        $isUpdateOnly = $this->request->get(self::UPDATE_MARKER, false);

        $form = $this->form;
        // take different form due to JS validation should be shown even in case when it was not validated on backend
        if ($isUpdateOnly) {
            $config = $form->getConfig();
            /** @var FormInterface $form */
            $form = $config->getFormFactory()->createNamed(
                $form->getName(),
                $config->getType()->getName(),
                $form->getData()
            );
        }

        return $form->createView();
    }

    /**
     * @return EntityManager
     */
    protected function getManager()
    {
        return $this->registry->getManager();
    }
}
