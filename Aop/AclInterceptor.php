<?php
namespace Oro\Bundle\UserBundle\Aop;

use CG\Proxy\MethodInterceptorInterface;
use CG\Proxy\MethodInvocation;
use Symfony\Component\HttpKernel\Log\LoggerInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use JMS\SecurityExtraBundle\Exception\RuntimeException;
use Symfony\Component\HttpFoundation\Response;


class AclInterceptor implements MethodInterceptorInterface
{
    private $context;
    private $logger;
    private $container;
    private $em;
    private $reader;

    public function __construct(SecurityContextInterface $context, LoggerInterface $logger, ContainerInterface $container)
    {
        $this->context = $context;
        $this->logger = $logger;
        $this->container = $container;
        $this->em = $container->get('doctrine')->getManager();
        $this->reader = $container->get('annotation_reader');
    }

    public function intercept(MethodInvocation $invocation)
    {
        /*$this->logger->info(sprintf('User invoked class: "%s" method "%s".', $invocation->reflection->class, $invocation->reflection->name));
        //get acl method resource name
        $aclAnnotation = $this->reader->getMethodAnnotation($invocation->reflection, 'Oro\Bundle\UserBundle\Annotation\Acl');

        if (!$invocation->reflection->name == 'testAction') {

        } else {
            //throw new RuntimeException('yessss');
        }
        return $invocation->proceed();*/
    }
}