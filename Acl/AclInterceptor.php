<?php
namespace Oro\Bundle\UserBundle\Acl;

use CG\Proxy\MethodInterceptorInterface;
use CG\Proxy\MethodInvocation;
use Symfony\Component\HttpKernel\Log\LoggerInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

use Oro\Bundle\UserBundle\Acl\Manager;
use Oro\Bundle\UserBundle\Entity\Acl;

class AclInterceptor implements MethodInterceptorInterface
{
    /**
     * @var \Symfony\Component\Security\Core\SecurityContextInterface
     */
    private $securityContext;

    /**
     * @var \Symfony\Component\HttpKernel\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    private $container;

    private $reader;
    private $accessDecisionManager;

    public function __construct(
        SecurityContextInterface $context,
        LoggerInterface $logger,
        ContainerInterface $container
    ) {
        $this->securityContext = $context;
        $this->logger = $logger;
        $this->container = $container;
        $this->reader = $container->get('annotation_reader');
        $this->accessDecisionManager = $container->get('security.access.decision_manager');
    }

    public function intercept(MethodInvocation $method)
    {
        $this->logger->info(
            sprintf('User invoked class: "%s", Method: "%s".', $method->reflection->class, $method->reflection->name)
        );

        //get acl method resource name
        $aclAnnotation = $this->reader->getMethodAnnotation(
            $method->reflection,
            Manager::ACL_ANNOTATION_CLASS
        );
        if (!$aclAnnotation) {
            $accessRoles = $this->getAclManager()->getAclRolesWithoutTree(Acl::ROOT_NODE);
        } else {
            $accessRoles = $this->getAclManager()->getAclRoles($aclAnnotation->getId());
        }

        $token = $this->securityContext->getToken();
        //var_dump($accessRoles); var_dump($token->getRoles());
        if (false === $this->accessDecisionManager->decide($token, $accessRoles, $method)) {

            //check if we have internal action - show blank
            if ($this->container->get('request')->attributes->get('_route') == '_internal') {
                return new Response('');
            }

            throw new AccessDeniedException('Access denied.');
        }

        return $method->proceed();
    }

    /**
     * @return \Oro\Bundle\UserBundle\Acl\Manager
     */
    public function getAclManager()
    {
        return $this->container->get('oro_user.acl_manager');
    }
}
