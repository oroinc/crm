<?php

namespace Oro\Bundle\WindowsBundle\Twig;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Twig_Environment;
use Twig_TemplateInterface;
use Twig_Template;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Symfony\Bundle\FrameworkBundle\Templating\TemplateReference;

class WindowsExtension extends \Twig_Extension
{
    const EXTENSION_NAME = 'oro_windows';
    const ROUTE_CONTROLLER_KEY = '_controller';
    const CONTROLLER_ACTION_DELIMITER = '::';

    /**
     * @var ContainerInterface $container
     */
    protected $container;

    /**
     * @var Twig_Environment $environment
     */
    protected $environment;

    /**
     * @param Twig_Environment $environment
     * @param ContainerInterface $container
     */
    public function __construct(
        Twig_Environment $environment,
        ContainerInterface $container
    ) {
        $this->container = $container;
        $this->environment = $environment;
    }

    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return array An array of functions
     */
    public function getFunctions()
    {
        return array(
            'oro_windows_render' => new \Twig_Function_Method($this, 'render', array('is_safe' => array('html')))
        );
    }

    /**
     * Renders a menu with the specified renderer.
     *
     * @param array $options
     *
     * @throws \InvalidArgumentException
     * @return string
     */
    public function render(array $options = array())
    {
        if (!($user = $this->getUser())) {
            return '';
        }

        $outputHtml = '';

        /** @var $httpKernel \Symfony\Bundle\FrameworkBundle\HttpKernel */
        $httpKernel = $this->container->get('http_kernel');

        /** @var $repo \Oro\Bundle\WindowsBundle\Entity\Repository\WindowsStateRepository */
        $repo = $this->getDoctrine()->getRepository('OroWindowsBundle:WindowsState');
        $windowsList = $repo->getWindowsStates($user->getId());

        if (count($windowsList)) {
            /** @var $window \Oro\Bundle\WindowsBundle\Entity\WindowsState */
            foreach ($windowsList as $window) {
                try {
                    if (empty($window['data']) || !($jsonData = json_decode($window['data'], true))) {
                        throw new \InvalidArgumentException("Window state data array can't be empty");
                    }

                    /** @var $parentRequest \Symfony\Component\HttpFoundation\Request */
                    $parentRequest = $this->container->get('request');

                    $url = $jsonData['url'];
                    $request = \Symfony\Component\HttpFoundation\Request::create($url);
                    $request->cookies->add($parentRequest->cookies->all());
                    $request->server->add(
                        array(
                            'SCRIPT_FILENAME' => $parentRequest->server->get('SCRIPT_FILENAME'),
                            'SCRIPT_NAME' => $parentRequest->server->get('SCRIPT_NAME'),
                            'PATH_INFO' => $url,
                        )
                    );

                    // Fill request object with router info
                    $responseEvent = new \Symfony\Component\HttpKernel\Event\GetResponseEvent(
                        $httpKernel,
                        $request,
                        HttpKernelInterface::MASTER_REQUEST
                    );
                    /** @var $routerListener \Symfony\Component\HttpKernel\EventListener\RouterListener */
                    $routerListener = $this->container->get('router_listener');
                    $routerListener->onKernelRequest($responseEvent);

                    // Add custom variables to template
                    $blockParams = array(
                        'stateId' => $window['id'],
                        'includeContainer' => true
                    );
                    $style = isset($jsonData['style']) ? $jsonData['style'] : '';
                    $style .= isset($options['style']) ? $options['style'] : '';
                    if ($style) {
                        $blockParams['containerStyle'] = $style;
                    }
                    if (isset($jsonData['id'])) {
                        $blockParams['containerId'] = $jsonData['id'];
                    }
                    if (isset($jsonData['title'])) {
                        $blockParams['containerTitle'] = $jsonData['title'];
                    }

                    $request->attributes->add(array('_template_rewrited_vars' => $blockParams));

                    // Call controller method
                    $response = $httpKernel->handle($request);
                    if ($response->getStatusCode() == 200) {
                        $outputHtml .= $response->getContent();
                    }
                } catch (\Exception $e) {
                    /** @var $entity \Oro\Bundle\WindowsBundle\Entity\WindowsState */
                    $entity = $this->getManager()->find('OroWindowsBundle:WindowsState', (int)$window['id']);
                    if ($entity) {
                        $em = $this->getManager();
                        $em->remove($entity);
                        $em->flush();
                    }
                }
            }

            $outputHtml .= $this->environment->render(
                "OroWindowsBundle::blockInit.html.twig",
                array("states" => $windowsList)
            );
        }

        return $outputHtml;
    }

    /**
     * Shortcut to return the Doctrine Registry service.
     *
     * @return Registry
     *
     * @throws \LogicException If DoctrineBundle is not available
     */
    public function getDoctrine()
    {
        if (!$this->container->has('doctrine')) {
            throw new \LogicException('The DoctrineBundle is not registered in your application.');
        }

        return $this->container->get('doctrine');
    }

    /**
     * Get entity Manager
     *
     * @return \Doctrine\Common\Persistence\ObjectManager
     */
    protected function getManager()
    {
        return $this->getDoctrine()->getEntityManagerForClass('OroWindowsBundle:WindowsState');
    }

    /**
     * Get a user from the Security Context
     *
     * @return null|mixed
     * @throws \LogicException If SecurityBundle is not available
     * @see Symfony\Component\Security\Core\Authentication\Token\TokenInterface::getUser()
     */
    public function getUser()
    {
        if (!$this->container->has('security.context')) {
            throw new \LogicException('The SecurityBundle is not registered in your application.');
        }

        /** @var $token \Symfony\Component\Security\Core\Authentication\Token\TokenInterface */
        if (null === $token = $this->container->get('security.context')->getToken()) {
            return null;
        }

        if (!is_object($user = $token->getUser())) {
            return null;
        }

        return $user;
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return self::EXTENSION_NAME;
    }
}
