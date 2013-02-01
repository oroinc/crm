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

    public function __construct(SecurityContextInterface $context, LoggerInterface $logger, ContainerInterface $container)
    {
        $this->securityContext = $context;
        $this->logger = $logger;
        $this->container = $container;
        $this->em = $container->get('doctrine')->getManager();
        $this->reader = $container->get('annotation_reader');
        $this->accessDecisionManager = $container->get('security.access.decision_manager');
    }

    public function intercept(MethodInvocation $method)
    {
        $this->logger->info(sprintf('User invoked class: "%s" method "%s".', $method->reflection->class, $method->reflection->name));
        //get acl method resource name
        $aclAnnotation = $this->reader->getMethodAnnotation($method->reflection, 'Oro\Bundle\UserBundle\Annotation\Acl');

        $token = $this->securityContext->getToken();
        if (false === $this->accessDecisionManager->decide($token, array(ROLE_SUPER_ADMIN), $method)) {
            throw new AccessDeniedException('Access denied.');
        }

        return $method->proceed();
    }
}