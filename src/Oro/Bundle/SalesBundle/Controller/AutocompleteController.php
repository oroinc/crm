<?php

namespace Oro\Bundle\SalesBundle\Controller;

use Oro\Bundle\FormBundle\Model\AutocompleteRequest;
use Oro\Bundle\SalesBundle\Autocomplete\CustomerSearchHandler;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\ConstraintViolation;

/**
 * @Route("/sales")
 */
class AutocompleteController extends AbstractController
{
    /**
     * @param Request $request
     * @param string $ownerClassAlias The owner class alias  for customers associations
     *
     * @return JsonResponse
     * @throws HttpException|AccessDeniedHttpException
     *
     * @Route("/customers/{ownerClassAlias}/search/autocomplete", name="oro_sales_customers_form_autocomplete_search")
     * @AclAncestor("oro_search")
     */
    public function autocompleteCustomersAction(Request $request, $ownerClassAlias)
    {
        $autocompleteRequest = new AutocompleteRequest($request);
        $validator           = $this->get('validator');
        $isXmlHttpRequest    = $request->isXmlHttpRequest();
        $code                = 200;
        $result              = [
            'results' => [],
            'hasMore' => false,
            'errors'  => []
        ];

        if ($violations = $validator->validate($autocompleteRequest)) {
            /** @var ConstraintViolation $violation */
            foreach ($violations as $violation) {
                $result['errors'][] = $violation->getMessage();
            }
        }

        if (!empty($result['errors'])) {
            if ($isXmlHttpRequest) {
                return new JsonResponse($result, $code);
            }

            throw new HttpException($code, implode(', ', $result['errors']));
        }

        /** @var CustomerSearchHandler $searchHandler */
        $searchHandler = $this->get('oro_sales.autocomplete.customer_search_handler');
        $searchHandler->setClass($this->get('oro_entity.routing_helper')->resolveEntityClass($ownerClassAlias));

        return new JsonResponse($searchHandler->search(
            $autocompleteRequest->getQuery(),
            $autocompleteRequest->getPage(),
            $autocompleteRequest->getPerPage(),
            $autocompleteRequest->isSearchById()
        ));
    }
}
