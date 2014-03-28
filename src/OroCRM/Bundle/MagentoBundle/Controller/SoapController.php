<?php

namespace OroCRM\Bundle\MagentoBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\IntegrationBundle\Provider\ConnectorInterface;

use OroCRM\Bundle\MagentoBundle\Provider\ExtensionAwareInterface;
use OroCRM\Bundle\MagentoBundle\Entity\MagentoSoapTransport;

class SoapController extends Controller
{
    /**
     * @Route("/check", name="orocrm_magento_soap_check")
     * @AclAncestor("oro_integration_channel_update")
     */
    public function checkAction(Request $request)
    {
        $transport = $this->get('orocrm_magento.transport.soap_transport');

        /*
         * Transport setting entity should be set to form
         * in case when password should be merged from saved data
         */
        $data = null;
        if ($id = $request->get('id', false)) {
            $data = $this->get('doctrine.orm.entity_manager')->find($transport->getSettingsEntityFQCN(), $id);
        }

        $form = $this->get('form.factory')
            ->createNamed('soap-check', $transport->getSettingsFormType(), $data, ['csrf_protection' => false]);
        $form->submit($request);

        /** @var MagentoSoapTransport $transportEntity */
        $transportEntity      = $form->getData();
        $websites             = $allowedTypesChoices = [];
        $isExtensionInstalled = false;
        try {
            $transport->init($transportEntity);
            $websites             = $this->formatWebsiteChoices($transport->getWebsites());

            $isExtensionInstalled = $transport->isExtensionInstalled();

            $allowedTypesChoices = $this->get('oro_integration.manager.types_registry')
                ->getAvailableConnectorsTypesChoiceList(
                    'magento',
                    function (ConnectorInterface $connector) use ($isExtensionInstalled) {
                        return $connector instanceof ExtensionAwareInterface ? $isExtensionInstalled : true;
                    }
                );
            $translator          = $this->get('translator');
            foreach ($allowedTypesChoices as $name => $val) {
                $allowedTypesChoices[$name] = $translator->trans($val);
            }
            $result = true;
        } catch (\Exception $e) {
            $result = false;
            $this->get('logger')->critical(sprintf('MageCheck error: %s: %s', $e->getCode(), $e->getMessage()));
        }

        return new JsonResponse(
            [
                'success'              => $result,
                'websites'             => $websites,
                'isExtensionInstalled' => $isExtensionInstalled,
                'connectors'           => $allowedTypesChoices,
            ]
        );
    }

    /**
     * Example:
     * [
     *      WebsiteId => 'Website: WebsiteId, Stores: Store1, Store2'
     * ]
     *
     * @param \Iterator $websitesSource
     *
     * @return array
     */
    protected function formatWebsiteChoices(\Iterator $websitesSource)
    {
        $translator = $this->get('translator');
        $websites   = iterator_to_array($websitesSource);
        $websites   = array_map(
            function ($website) use ($translator) {
                return [
                    'id'    => $website['id'],
                    'label' => $translator->trans(
                        'Website ID: %websiteId%, Stores: %storesList%',
                        [
                            '%websiteId%'  => $website['id'],
                            '%storesList%' => $website['name']
                        ]
                    )
                ];
            },
            $websites
        );

        return $websites;
    }
}
