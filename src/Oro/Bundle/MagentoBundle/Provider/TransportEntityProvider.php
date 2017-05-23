<?php

namespace Oro\Bundle\MagentoBundle\Provider;

use Doctrine\ORM\EntityManager;

use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;

use Oro\Bundle\IntegrationBundle\Provider\TransportInterface;
use Oro\Bundle\MagentoBundle\Entity\MagentoTransport;
use Oro\Bundle\MagentoBundle\Provider\Transport\MagentoTransportInterface;

class TransportEntityProvider
{
    const ENTITY_ID = 'id';
    const FORM_NAME = 'check';

    /** @var  FormFactoryInterface */
    protected $formFactory;

    /** @var  Request */
    protected $entityManager;

    /**
     * @param FormFactoryInterface $formFactory
     * @param EntityManager $entityManager
     */
    public function __construct(FormFactoryInterface $formFactory, EntityManager $entityManager)
    {
        $this->formFactory      = $formFactory;
        $this->entityManager    = $entityManager;
    }

    /**
     * Transport setting entity should be set to form
     * in case when password should be merged from saved data
     *
     * @param MagentoTransportInterface $transport
     * @param Request                   $request
     * @return MagentoTransport
     */
    public function getTransportEntityByRequest(MagentoTransportInterface $transport, Request $request)
    {
        $entityId = $request->get(self::ENTITY_ID, false);

        $data = $entityId ? $this->findTransportEntity($transport->getSettingsEntityFQCN(), $entityId) : null;

        $form = $this->formFactory
            ->createNamed(
                self::FORM_NAME,
                $transport->getSettingsFormType(),
                $data,
                ['csrf_protection' => false]
            );

        $form->handleRequest($request);

        return $form->getData();
    }

    /**
     * @param string|TransportInterface $settingsEntity
     * @param string                    $entityId
     * @return null|object
     * @throws \LogicException
     */
    public function findTransportEntity($settingsEntity, $entityId)
    {
        if ($settingsEntity instanceof TransportInterface) {
            $settingsEntityFQCN = $settingsEntity->getSettingsEntityFQCN();
        } elseif (!is_object($settingsEntity)) {
            $settingsEntityFQCN = $settingsEntity;
        } else {
            throw new \LogicException(
                sprintf('$settingsEntity must be string or instanceof %s', TransportInterface::class)
            );
        }

        return $this->entityManager->find($settingsEntityFQCN, $entityId);
    }
}
