<?php
namespace Oro\Bundle\NavigationBundle\Security\EntryPoint;

use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

/**
 * ApiAuthenticationEntryPoint
 */
class ApiAuthenticationEntryPoint implements AuthenticationEntryPointInterface
{
    public function start(Request $request, AuthenticationException $authException = null)
    {
        $response = new Response();
        $response->setStatusCode(401, $authException ? $authException->getMessage() : null);

        if ($request->getContentType() == 'application/json' && $authException) {
            $response->headers->set('Content-Type', 'application/json');
            $response->setContent(
                json_encode(array('message' => $authException->getMessage()))
            );
        }

        return $response;
    }
}
