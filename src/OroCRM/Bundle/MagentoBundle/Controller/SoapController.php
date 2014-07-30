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
use OroCRM\Bundle\MagentoBundle\Provider\Iterator\StoresSoapIterator;

class SoapController extends Controller
{
    /**
     * @Route("/check", name="orocrm_magento_soap_check")
     * @AclAncestor("oro_integration_update")
     */
    public function checkAction(Request $request)
    {
        $transport = $this->get('orocrm_magento.transport.soap_transport');
        $transport->setMultipleAttemptsEnabled(false);

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
        $adminUrl             = false;
        try {
            $transport->init($transportEntity);
            $isExtensionInstalled = $transport->isExtensionInstalled();
            $websites             = $this->formatWebsiteChoices($transport->getWebsites());
            $adminUrl             = $transport->getAdminUrl();
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
                'adminUrl'             => $adminUrl,
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

        // Delete Admin website
        foreach ($websites as $key => $website) {
            if ($website['id'] == StoresSoapIterator::ADMIN_WEBSITE_ID) {
                unset($websites[$key]);
            }
        }

        // Add all web sites choice
        array_unshift(
            $websites,
            [
                'id' => StoresSoapIterator::ALL_WEBSITES,
                'label' => $translator->trans('orocrm.magento.magentosoaptransport.all_sites')
            ]
        );

        return $websites;
    }
}
