<?php

namespace Oro\Bundle\MagentoBundle\Provider;

use Doctrine\Common\Persistence\ManagerRegistry;
use Oro\Bundle\IntegrationBundle\Provider\TransportInterface;
use Oro\Bundle\MagentoBundle\Entity\MagentoTransport;
use Oro\Bundle\MagentoBundle\Provider\Transport\MagentoTransportInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;

class TransportEntityProvider
{
    const ENTITY_ID = 'id';
    const FORM_NAME = 'check';

    /** @var  FormFactoryInterface */
    protected $formFactory;

    /** @var  ManagerRegistry */
    protected $registry;

    /**
     * @param FormFactoryInterface $formFactory
     * @param ManagerRegistry $registry
     */
    public function __construct(FormFactoryInterface $formFactory, ManagerRegistry $registry)
    {
        $this->formFactory = $formFactory;
        $this->registry    = $registry;
    }

    /**
     * Transport setting entity should be set to form
     * in case when password should be merged from saved data
     *
     * @param MagentoTransportInterface $transport
     * @param Request                   $request
     *
     * @return MagentoTransport
     */
    public function getTransportEntityByRequest(MagentoTransportInterface $transport, Request $request)
    {
        $data = null;
        $entityId = $request->get(self::ENTITY_ID);

        if (null !== $entityId) {
            $data = $this->findTransportEntity($transport, $entityId);
        }

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
     * @param TransportInterface $settingsEntity
     * @param string                    $entityId
     *
     * @return object
     */
    protected function findTransportEntity(TransportInterface $settingsEntity, $entityId)
    {
        $className = $settingsEntity->getSettingsEntityFQCN();
        $em = $this->registry->getManagerForClass($className);

        return $em->find($className, $entityId);
    }
}
