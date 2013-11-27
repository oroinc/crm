<?php

namespace OroCRM\Bundle\MagentoBundle\Controller;

use Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Persistence\AclPrivilegeRepositoryTest;
use OroCRM\Bundle\MagentoBundle\Provider\CustomerConnectorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use OroCRM\Bundle\MagentoBundle\Entity\MagentoSoapTransport;

class SoapController extends Controller
{
    /**
     * @Route("/check")
     * @AclAncestor("oro_integration_channel_update")
     */
    public function checkAction(Request $request)
    {
        $transport            = $this->get('orocrm_magento.mage.soap_transport');
        $transportSavedEntity = $request->get('id', false);

        $data = null;
        if ($transportSavedEntity) {
            $data = $this->get('doctrine.orm.entity_manager')
                ->find($transport->getSettingsEntityFQCN(), $transportSavedEntity);
        }

        $form = $this->get('form.factory')->createNamed(
            'soap-check',
            $transport->getSettingsFormType(),
            $data,
            ['csrf_protection' => false]
        );

        $form->submit($request);

        /** @var MagentoSoapTransport $transportEntity */
        $transportEntity = $form->getData();
        $stores          = [];
        try {
            $result = $transport->init($transportEntity->getSettingsBag());
            $stores = $transport->call(CustomerConnectorInterface::ACTION_STORE_LIST);
            $stores = array_map(
                function ($item) {
                    return [
                        'id'   => $item->store_id,
                        'name' => $item->name
                    ];
                },
                $stores
            );

        } catch (\Exception $e) {
            $result = false;
        }

        return new JsonResponse(['success' => $result, 'stores' => $stores]);
    }
}
