<?php

namespace Oro\Bundle\MagentoBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\MagentoBundle\Exception\RuntimeException;
use Oro\Bundle\MagentoBundle\Exception\ExtensionRequiredException;
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
        } catch (ExtensionRequiredException $e) {
            $this->logException($e);
            $response = $this->createFailResponse(
                $this->get('translator')->trans('oro.magento.controller.extension_required')
            );
        } catch (RuntimeException $e) {
            $this->logException($e);
            $response = $this->createFailResponse(
                $this->get('translator')->trans('oro.magento.controller.transport_error')
            );
        } catch (\Exception $e) {
            $this->logException($e);
            $response = $this->createFailResponse(
                $this->get('translator')->trans('oro.magento.controller.not_valid_parameters')
            );
        }

        return new JsonResponse($response);
    }

    /**
     * @param \Exception $exception
     */
    protected function logException(\Exception $exception)
    {
        $message = ValidationUtils::sanitizeSecureInfo($exception->getMessage());
        $this->get('logger')->critical(sprintf('MageCheck error: %s: %s', $exception->getCode(), $message));
    }

    /**
     * @param string    $message
     * @return array
     */
    protected function createFailResponse($message)
    {
        $response = [
            'success'      => false,
            'errorMessage' => $message
        ];

        return $response;
    }
}
