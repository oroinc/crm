<?php

namespace Oro\Bundle\SalesBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Validator\ConstraintViolation;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SalesBundle\Autocomplete\CustomerSearchHandler;
use Oro\Bundle\FormBundle\Model\AutocompleteRequest;

/**
 * @Route("/sales/customers")
 */
class AutocompleteController extends Controller
{
    /**
     * @param Request $request
     * @param string $ownerClass The owner class for customers associations
     *
     * @return JsonResponse
     * @throws HttpException|AccessDeniedHttpException
     *
     * @Route("/{ownerClass}/search/autocomplete", name="oro_sales_customers_form_autocomplete_search")
     * @AclAncestor("oro_search")
     */
    public function autocompleteCustomersAction(Request $request, $ownerClass)
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
        $searchHandler->setClass($ownerClass);

        return new JsonResponse($searchHandler->search(
            $autocompleteRequest->getQuery(),
            $autocompleteRequest->getPage(),
            $autocompleteRequest->getPerPage(),
            $autocompleteRequest->isSearchById()
        ));
    }
}
