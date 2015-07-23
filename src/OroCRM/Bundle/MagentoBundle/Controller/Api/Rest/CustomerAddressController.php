<?php

namespace OroCRM\Bundle\MagentoBundle\Controller\Api\Rest;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

use Doctrine\ORM\EntityNotFoundException;

use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\RestBundle\Util\Codes;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\AddressBundle\Entity\AbstractTypedAddress;
use Oro\Bundle\SoapBundle\Controller\Api\Rest\RestController;
use Oro\Bundle\SecurityBundle\Exception\ForbiddenException;

use OroCRM\Bundle\MagentoBundle\Entity\Address;
use OroCRM\Bundle\MagentoBundle\Entity\Customer;
use OroCRM\Bundle\MagentoBundle\Entity\Manager\CustomerApiEntityManager;

/**
 * @NamePrefix("oro_api_")
 */
class CustomerAddressController extends RestController implements ClassResourceInterface
{
    /**
     * Get all addresses items.
     *
     * @ApiDoc(
     *      description="Get all addresses items",
     *      resource=true
     * )
     * @AclAncestor("orocrm_magento_customer_view")
     * @param int $customerId
     *
     * @return JsonResponse
     */
    public function cgetAction($customerId)
    {
        /** @var Customer $customer */
        $customer = $this->getCustomerManager()->find($customerId);
        $result   = [];

        if ($customer instanceof Customer) {
            $items = $customer->getAddresses();

            foreach ($items as $item) {
                $result[] = $this->getPreparedItem($item);
            }
        }

        return new JsonResponse(
            $result,
            empty($customer) ? Codes::HTTP_NOT_FOUND : Codes::HTTP_OK
        );
    }

    /**
     * Add address to the customer.
     *
     * @ApiDoc(
     *      description="Add address to the customer",
     *      resource=true
     * )
     * @AclAncestor("orocrm_magento_customer_create")
     * @param int $customerId
     *
     * @return JsonResponse
     */
    public function postAction($customerId)
    {
        /** @var Customer $customer */
        $customer    = $this->getCustomerManager()->find($customerId);
        $isProcessed = false;
        $entity      = new Address();

        if ($customer instanceof Customer) {
            $entity = $this->processForm($entity, $customer);
            if ($entity) {
                $view = $this->view($this->createResponseData($entity), Codes::HTTP_CREATED);
                $isProcessed = true;
            } else {
                $view = $this->view($this->getForm(), Codes::HTTP_BAD_REQUEST);
            }

        } else {
            $view = $this->view($this->getForm(), Codes::HTTP_NOT_FOUND);
        }

        return $this->buildResponse($view, self::ACTION_CREATE, ['success' => $isProcessed, 'entity' => $entity]);
    }

    /**
     * Get customer address.
     *
     * @param int $addressId
     * @param int $customerId
     *
     * @ApiDoc(
     *      description="Get customer address",
     *      resource=true
     * )
     * @AclAncestor("orocrm_magento_customer_view")
     *
     * @return Response
     */
    public function getAction($customerId, $addressId)
    {
        $address = $this->getManager()->serializeElement($customerId, $addressId);

        return new JsonResponse(
            $address,
            empty($address) ? Codes::HTTP_NOT_FOUND : Codes::HTTP_OK
        );
    }

    /**
     * Update customer address.
     *
     * @param int $addressId  address item id
     * @param int $customerId customer item id
     *
     * @ApiDoc(
     *      description="Update customer address",
     *      resource=true
     * )
     * @AclAncestor("orocrm_magento_customer_update")
     * @return Response
     */
    public function putAction($customerId, $addressId)
    {
        $address = $this->getManager()->findOneBy(['owner' => $customerId, 'id' => $addressId]);

        if ($address) {
            if ($this->processForm($address)) {
                $view = $this->view(null, Codes::HTTP_NO_CONTENT);
            } else {
                $view = $this->view($this->getForm(), Codes::HTTP_BAD_REQUEST);
            }
        } else {
            $view = $this->view(null, Codes::HTTP_NOT_FOUND);
        }

        return $this->buildResponse($view, self::ACTION_UPDATE, ['id' => $addressId, 'entity' => $address]);
    }

    /**
     * Delete customer address.
     *
     * @param int $addressId  address item id
     * @param int $customerId customer item id
     *
     * @ApiDoc(
     *      description="Delete customer address",
     *      resource=true
     * )
     * @Acl(
     *      id="orocrm_magento_customer_delete",
     *      type="entity",
     *      permission="DELETE",
     *      class="OroCRMMagentoBundle:Address"
     * )
     * @return Response
     */
    public function deleteAction($customerId, $addressId)
    {
        $isProcessed = false;
        $address     = $this->getManager()->findOneBy(['owner' => $customerId, 'id' => $addressId]);

        if (!$address) {
            $view = $this->view(null, Codes::HTTP_NOT_FOUND);
        } else {
            try {
                $this->getDeleteHandler()->handleDelete($addressId, $this->getManager());

                $isProcessed = true;
                $view        = $this->view(null, Codes::HTTP_NO_CONTENT);
            } catch (EntityNotFoundException $notFoundEx) {
                $view = $this->view(null, Codes::HTTP_NOT_FOUND);
            } catch (ForbiddenException $forbiddenEx) {
                $view = $this->view(['reason' => $forbiddenEx->getReason()], Codes::HTTP_FORBIDDEN);
            }
        }

        return $this->buildResponse($view, self::ACTION_DELETE, ['id' => $addressId, 'success' => $isProcessed]);
    }

    /**
     * {@inheritdoc}
     */
    public function getManager()
    {
        return $this->get('orocrm_magento.customer_address.manager.api');
    }

    /**
     * {@inheritdoc}
     */
    public function getForm()
    {
        return $this->get('orocrm_magento.form.customer_address.api');
    }

    /**
     * {@inheritdoc}
     */
    public function getFormHandler()
    {
        return $this->get('orocrm_magento.form.api.handler.customer_address');
    }

    /**
     * @return CustomerApiEntityManager
     */
    protected function getCustomerManager()
    {
        return $this->get('orocrm_magento.customer.manager.api');
    }

    /**
     * {@inheritDoc}
     */
    protected function getPreparedItem($entity, $resultFields = [])
    {
        // convert addresses to plain array
        $addressTypesData = [];

        /** @var $entity AbstractTypedAddress */
        foreach ($entity->getTypes() as $addressType) {
            $addressTypesData[] = parent::getPreparedItem($addressType);
        }

        $result                = parent::getPreparedItem($entity);
        $result['types']       = $addressTypesData;
        $result['countryIso2'] = $entity->getCountryIso2();
        $result['countryIso3'] = $entity->getCountryIso3();
        $result['regionCode']  = $entity->getRegionCode();
        $result['country'] = $entity->getCountryName();

        unset($result['owner']);

        return $result;
    }
}
