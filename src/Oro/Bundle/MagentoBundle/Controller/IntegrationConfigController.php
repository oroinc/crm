<?php

namespace Oro\Bundle\MagentoBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\MagentoBundle\Utils\ValidationUtils;

class IntegrationConfigController extends Controller
{
    /**
     * @return JsonResponse
     *
     * @Route("/check", name="oro_magento_integration_check")
     * @AclAncestor("oro_integration_update")
     */
    public function checkAction()
    {
        $handler = $this->get('oro_magento.handler.transport');

        try {
            $response = $handler->getCheckResponse();
        } catch (\Exception $e) {
            $message = ValidationUtils::sanitizeSecureInfo($e->getMessage());
            $this->get('logger')->critical(sprintf('MageCheck error: %s: %s', $e->getCode(), $message));
            $response = $handler->createFailResponse($message, $e->getCode());
        }

        return new JsonResponse($response);
    }
}
