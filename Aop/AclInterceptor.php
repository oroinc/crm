<?php
namespace Oro\Bundle\UserBundle\Aop;

use CG\Proxy\MethodInterceptorInterface;
use CG\Proxy\MethodInvocation;
use Symfony\Component\HttpKernel\Log\LoggerInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use JMS\SecurityExtraBundle\Exception\RuntimeException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

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

    private $em;
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
        $this->em = $container->get('doctrine')->getManager();
        $this->reader = $container->get('annotation_reader');
        $this->accessDecisionManager = $container->get('security.access.decision_manager');
    }

    public function intercept(MethodInvocation $method)
    {
        $this->logger->info(
            sprintf('User invoked class: "%s" method "%s".', $method->reflection->class, $method->reflection->name)
        );

        //get acl method resource name
        $aclAnnotation = $this->reader->getMethodAnnotation(
            $method->reflection,
            'Oro\Bundle\UserBundle\Annotation\Acl'
        );

        $aclPath = $this->getAclManager()->getAclNodePath($aclAnnotation->getId());
        $accessRoles = array();
        foreach ($aclPath as $acl) {
            /** @var \Oro\Bundle\UserBundle\Entity\Acl $acl */
            $roles = $acl->getAccessRolesNames();
            $accessRoles = array_unique(array_merge($roles, $accessRoles));
        }


        $token = $this->securityContext->getToken();
        if (false === $this->accessDecisionManager->decide($token, $accessRoles, $method)) {

            /** @var $request \Symfony\Component\HttpFoundation\Request */
            $request = $this->container->get('request');

            //check if we have internal action - show blank
            if($request->attributes->get('_route') == '_internal') {

                return new Response('');
            }
            throw new AccessDeniedException('Access denied.');
        }

        return $method->proceed();
    }

    /**
     * @return \Oro\Bundle\UserBundle\Aop\Manager
     */
    public function getAclManager()
    {
        return $this->container->get('oro_user.acl_manager');
    }

}