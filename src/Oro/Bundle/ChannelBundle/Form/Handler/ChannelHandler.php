<?php

namespace Oro\Bundle\ChannelBundle\Form\Handler;

use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\ChannelBundle\Event\ChannelSaveEvent;
use Oro\Bundle\ChannelBundle\Form\Type\ChannelType;
use Oro\Bundle\FormBundle\Form\Handler\RequestHandlerTrait;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class ChannelHandler
{
    use RequestHandlerTrait;

    const UPDATE_MARKER = 'formUpdateMarker';

    /** @var RequestStack */
    protected $requestStack;

    /** @var ManagerRegistry */
    protected $registry;

    /** @var FormInterface */
    protected $form;

    /** @var EventDispatcherInterface */
    protected $dispatcher;

    public function __construct(
        RequestStack $requestStack,
        FormInterface $form,
        ManagerRegistry $registry,
        EventDispatcherInterface $dispatcher
    ) {
        $this->requestStack = $requestStack;
        $this->form = $form;
        $this->registry = $registry;
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

        $request = $this->requestStack->getCurrentRequest();
        if (in_array($request->getMethod(), ['POST', 'PUT'], true)) {
            $this->submitPostPutRequest($this->form, $request);

            if (!$request->get(self::UPDATE_MARKER, false) && $this->form->isValid()) {
                $this->doSave($entity);

                return true;
            }
        }

        return false;
    }

    protected function handleRequestChannelType(Channel &$channel)
    {
        if ($channel->getChannelType()) {
            return;
        }

        $formData = $this->requestStack->getCurrentRequest()->get(ChannelType::NAME);
        $channelType = $formData['channelType'] ?? null;

        if (!$channelType) {
            return;
        }

        $channel->setChannelType($channelType);
    }

    /**
     * Saves entity and dispatches needed events
     */
    protected function doSave(Channel $entity)
    {
        $this->getManager()->persist($entity);
        $this->getManager()->flush();

        $this->dispatcher->dispatch(new ChannelSaveEvent($entity), ChannelSaveEvent::EVENT_NAME);
    }

    /**
     * Returns form instance
     *
     * @return FormInterface
     */
    public function getFormView()
    {
        $isUpdateOnly = $this->requestStack
            ->getCurrentRequest()
            ->get(self::UPDATE_MARKER, false);

        $form = $this->form;
        // take different form due to JS validation should be shown even in case when it was not validated on backend
        if ($isUpdateOnly) {
            $config = $form->getConfig();
            /** @var FormInterface $form */
            $form = $config->getFormFactory()->createNamed(
                $form->getName(),
                get_class($config->getType()->getInnerType()),
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
