<?php

namespace Oro\Bundle\SalesBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Validator\ConstraintViolation;

use Oro\Bundle\SalesBundle\Autocomplete\OpportunityCustomerSearchHandler;
use Oro\Bundle\FormBundle\Model\AutocompleteRequest;

/**
 * @Route("/sales/autocomplete")
 */
class AutocompleteController extends Controller
{
    /**
     * @param Request $request
     *
     * @return JsonResponse
     * @throws HttpException
     *
     * @Route("/customers", name="oro_sales_autocomplete_customers")
     */
    public function autocompleteCustomersAction(Request $request)
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

        /** @var OpportunityCustomerSearchHandler $searchHandler */
        $searchHandler = $this->get('oro_sales.autocomplete.customer_search_handler');

        return new JsonResponse($searchHandler->search(
            $autocompleteRequest->getQuery(),
            $autocompleteRequest->getPage(),
            $autocompleteRequest->getPerPage(),
            $autocompleteRequest->isSearchById()
        ));
    }
}
