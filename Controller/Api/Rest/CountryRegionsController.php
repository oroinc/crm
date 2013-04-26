<?php

namespace Oro\Bundle\AddressBundle\Controller\Api\Rest;

use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\Rest\Util\Codes;

/**
 * @RouteResource("country/regions")
 * @NamePrefix("oro_api_country_")
 */
class CountryRegionsController extends FOSRestController
{
    /**
     * REST GET regions by country
     *
     * @param string $id
     *
     * @ApiDoc(
     *  description="Get regions by country id",
     *  resource=true
     * )
     * @return Response
     */
    public function getAction($id)
    {
        $item = $this->getDoctrine()->getRepository('OroAddressBundle:Country')->find($id);

        return $this->handleView(
            $this->view($item->getRegions(), is_object($item) ? Codes::HTTP_OK : Codes::HTTP_NOT_FOUND)
        );
    }
}
